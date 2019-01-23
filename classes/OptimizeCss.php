<?php
namespace WpAssetCleanUp;

/**
 * Class OptimizeCss
 * @package WpAssetCleanUp
 */
class OptimizeCss
{
	/**
	 * @var string
	 */
	public static $relPathCssCacheDir = '/cache/asset-cleanup/css/'; // keep trailing slash at the end

	/**
	 * @var string
	 */
	public static $transientCssNamePrefix = 'wpacu_css_';

	/**
	 * @var float|int
	 */
	private static $_transientExpiresIn = 60 * 60 * 12; // 8 hours in seconds

	/**
	 * @var
	 */
	private $_transientCssName;

	/**
	 * OptimizeCss constructor.
	 */
	public function __construct()
	{
		add_action('switch_theme',               array($this, 'clearAllCacheTransients'));
		add_action('after_switch_theme',         array($this, 'clearAllCacheTransients'));

		// Is WP Rocket's page cache cleared? Clear Asset CleanUp's CSS cache files too
		if (array_key_exists('action', $_GET) && $_GET['action'] === 'purge_cache') {
			add_action( 'before_rocket_clean_domain', array( $this, 'clearAllCacheTransients' ) );
		}

		// Is the CSS cache transient deleted? Remove the cached CSS file too
		// The action below is triggered prior to transient deletion and ONLY in the front-end view (not within the Dashboard)
		add_action('plugins_loaded', array($this, 'afterPluginsLoaded'));

		add_action('admin_post_assetcleanup_clear_assets_cache', function() {
			$this->clearAllCacheTransients(true);
		});
	}

	/**
	 *
	 */
	public function afterPluginsLoaded()
	{
		if (! is_admin() && (strpos($_SERVER['REQUEST_URI'], '?') === false)) {
			$toMdFive = $_SERVER['REQUEST_URI'];

			if (is_user_logged_in()) {
				global $current_user;

				if (isset($current_user->ID) && $current_user->ID > 0) {
					$toMdFive .= '_'.$current_user->ID;
				}
			}

			$this->_transientCssName = self::$transientCssNamePrefix . md5( $toMdFive );

			// @TODO: Remove cached CSS files after a while making sure no caching system still loads the old file
			// @TODO: Maybe adding to "Tools" page would be an option
			//add_action( 'delete_transient_' . $this->transientCssName, array( $this, 'clearCssCacheFile' ) );
		}
	}

	/**
	 *
	 */
	public function init()
	{
		add_action('wp_loaded', function() {
			if (! $this->doCssCombine()) {
				return;
			}

			ob_start(function ($htmlSource) {
				$useDom = function_exists('libxml_use_internal_errors') && function_exists('libxml_clear_errors') && class_exists('DOMDocument');

				if (! $useDom) {
					return $htmlSource;
				}

				$cssCachedFileExists = false;

				// Speed up processing by getting the already existing final CSS file URI
				// This will avoid parsing the HTML DOM and determine the combined URI paths for all the CSS files
				$localFinalCssFileData = $this->getCssCachedTransient();

				// $uriToFinalCssFile will always be relative ONLY within WP_CONTENT_DIR . self::$relPathCssCacheDir
				// which is usually "wp-content/cache/asset-cleanup/css/"

				if (! empty($localFinalCssFileData) && isset($localFinalCssFileData['local_final_css_file']) && file_exists($localFinalCssFileData['local_final_css_file'])) {
					/*
					 * URIs to the LINK tags are already cached; No need to parse the DOM
					*/
					$uriToFinalCssFile = $localFinalCssFileData['uri_final_css_file'];
					$linkHrefs = $localFinalCssFileData['link_hrefs'];
					$cssCachedFileExists = true;
				} else {
					/*
					 * NO CACHING TRANSIENT; Parse the DOM
					*/
					// Nothing in the database records or the retrieved cached file does not exist?
					$this->deleteCssCachedTransient();

					// Fetch the DOM, and then set a new transient
					$document = new \DOMDocument();
					$document->loadHTML($htmlSource);

					$documentHead = $document->getElementsByTagName('head')->item(0);

					libxml_use_internal_errors( true );

					$combinedUriPaths = $hrefUriPreloads = $localAssetsPaths = $linkHrefs = array();

					foreach ($documentHead->getElementsByTagName('link') as $tagObject) {
						if (! $tagObject->hasAttributes()) {
							continue;
						}

						$getHref = $checkCssPreload = $href = false;

						foreach ($tagObject->attributes as $attrObj) {
							// Only rel="stylesheet" (with no rel="preload" associated with it) gets prepared for combining as links with rel="preload" (if any) are never combined into a standard render-blocking CSS file
							// rel="preload" is there for a reason to make sure the CSS code is made available earlier prior to the one from rel="stylesheet" which is render-blocking
							if ($attrObj->nodeName === 'rel' && $attrObj->nodeValue === 'stylesheet') {
								$getHref = true;
							}

							if ($getHref && $attrObj->nodeName === 'href') {
								// Make sure that tag value is checked and it's matched against the value from the HTML source code
								//$htmlSource .= $attrObj->nodeValue."\n";
								$href = (string)$attrObj->nodeValue;

								$localAssetPath = self::getLocalAssetPath($href);

								if ($localAssetPath) {
									$combinedUriPaths[] = self::getHrefRelPath($href);
									$localAssetsPaths[$href] = $localAssetPath;
									$linkHrefs[] = $href;
								}
							}

							// Check if there is any rel="preload" connected to the rel="stylesheet"
							// and make sure the file is not added to the final CSS combined file
							if ($attrObj->nodeName === 'rel' && $attrObj->nodeValue === 'preload') {
								$checkCssPreload = true;
							}

							if ($checkCssPreload && $attrObj->nodeName === 'href') {
								$hrefUriPreloads[] = self::getHrefRelPath($attrObj->nodeValue);
							}
						}
					}

					// Any rel="preload" found? Remove the stylesheet from the combination
					if (! empty($hrefUriPreloads)) {
						foreach ($hrefUriPreloads as $hrefUriPreload) {
							if (in_array($hrefUriPreload, $combinedUriPaths)) {
								$linkHrefUriKey = array_search($hrefUriPreload, $combinedUriPaths);
								unset($combinedUriPaths[$linkHrefUriKey]);

								foreach (array_keys($localAssetsPaths) as $localAssetPathKey) {
									if (substr($localAssetPathKey, -strlen($hrefUriPreload)) === $hrefUriPreload) {
										unset($localAssetsPaths[$localAssetPathKey]);
									}
								}

								foreach ($linkHrefs as $linkHrefKey => $linkHref) {
									if (substr($linkHref, -strlen($hrefUriPreload)) === $hrefUriPreload) {
										unset($linkHrefs[$linkHrefKey]);
									}
								}
							}
						}
					}

					// No Link Tags? Just return output
					if (empty($linkHrefs)) {
						return $htmlSource;
					}

					$maybeDoCssCombine = $this->maybeDoCssCombine( sha1( implode('', $combinedUriPaths) ), $localAssetsPaths );

					// Local path to combined CSS file
					$localFinalCssFile = $maybeDoCssCombine['local_final_css_file'];

					// URI (e.g. /wp-content/cache/asset-cleanup/[file-name-here.css]) to the combined CSS file
					$uriToFinalCssFile = $maybeDoCssCombine['uri_final_css_file'];

					if (file_exists($localFinalCssFile)) {
						$cssCachedFileExists = true;
						$this->setCssCachedTransient( $uriToFinalCssFile, $linkHrefs );
					}
				}

				if ($cssCachedFileExists) {
					$finalTagUrl = WP_CONTENT_URL . self::$relPathCssCacheDir . $uriToFinalCssFile;

					$finalCssTag = <<<HTML
<link id="asset-cleanup-combined-css" rel="stylesheet" href="{$finalTagUrl}" type="text/css" media="all" />
HTML;

					// Append the combine CSS Link tag right after <head> section of the website
					// Any other <head> strings will be ignored as only the first one from the HTML source code matters
					// e.g. some may use <head> inside a JavaScript code or part of a comment
					$htmlSource = preg_replace('#<head>#si', '<head>'."\n" . $finalCssTag . "\n", $htmlSource, 1);
					$htmlSource = $this->stripJustCombinedSingleLinkTags($linkHrefs, $htmlSource); // Strip the combined files to avoid duplicate code
				}

				return $htmlSource;
			});
		}, 1);
	}

	/**
	 * @param $linkHrefs
	 * @param $htmlSource
	 *
	 * @return mixed
	 */
	public function stripJustCombinedSingleLinkTags($linkHrefs, $htmlSource)
	{
		preg_match_all('#<link[^>]*stylesheet[^>]*(>)#Usmi', $htmlSource, $matchesLinkStylesheetTags, PREG_SET_ORDER);

		foreach ($matchesLinkStylesheetTags as $matchLinkStylesheetTag) {
			$matchedLinkStylesheetTag = $matchLinkStylesheetTag[0];

			$domLink = new \DOMDocument();
			$domLink->loadHTML($matchedLinkStylesheetTag);

			foreach ($domLink->getElementsByTagName( 'link' ) as $tagLinkObject) {
				if (! $tagLinkObject->hasAttributes()) {
					continue;
				}

				foreach ($tagLinkObject->attributes as $tagLinkAttrs) {
					if ($tagLinkAttrs->nodeName === 'href' && in_array($tagLinkAttrs->nodeValue, $linkHrefs)) {
						$htmlSource = str_replace($matchedLinkStylesheetTag, '', $htmlSource);
					}
				}
			}
		}

		return $htmlSource;
	}

	/**
	 * @param $shaOneCombinedUriPaths
	 * @param $localAssetsPaths
	 *
	 * @return array
	 */
	public function maybeDoCssCombine($shaOneCombinedUriPaths, $localAssetsPaths)
	{
		$current_user = wp_get_current_user();
		$dirToUserCachedFile = ((isset($current_user->ID) && $current_user->ID > 0) ? 'logged-in/'.$current_user->ID.'/' : '');

		$uriToFinalCssFile = $dirToUserCachedFile . $shaOneCombinedUriPaths . '.css';
		$localFinalCssFile = WP_CONTENT_DIR . self::$relPathCssCacheDir . $uriToFinalCssFile;

		$localDirForCssFile = WP_CONTENT_DIR . self::$relPathCssCacheDir . $dirToUserCachedFile;

		// Only combine if $shaOneCombinedUriPaths.css does not exist
		// If "?ver" value changes on any of the assets or the asset list changes in any way
		// then $shaOneCombinedUriPaths will change too and a new CSS file will be generated and loaded

		// || defined('WPACU_DEV_ALWAYS_GENERATE_COMBINED_CSS'
		if (! file_exists($localFinalCssFile)) {
			// Change $assetsContents as paths to fonts and images that are relative (e.g. ../, ../../) have to be updated
			$finalAssetsContents = '';

			foreach ($localAssetsPaths as $assetHref => $localAssetsPath) {
				$posLastSlash = strrpos($assetHref, '/');
				$pathToAssetDir = substr($assetHref, 0, $posLastSlash);

				$parseUrl = parse_url($pathToAssetDir);

				if (isset($parseUrl['scheme']) && $parseUrl['scheme'] !== '') {
					$pathToAssetDir = str_replace(
						array('http://'.$parseUrl['host'], 'https://'.$parseUrl['host']),
						'',
						$pathToAssetDir
					);
				} elseif (strpos($pathToAssetDir, '//') === 0) {
					$pathToAssetDir = str_replace(
						array('//'.$parseUrl['host'], '//'.$parseUrl['host']),
						'',
						$pathToAssetDir
					);
				}

				$assetContent = @file_get_contents($localAssetsPath);

				if ($assetContent) {
					$finalAssetsContents .= $this->maybeFixCssBackgroundUrls($assetContent, $pathToAssetDir . '/') . "\n\n";
				}
			}

			$finalAssetsContents = trim($finalAssetsContents);

			if ($finalAssetsContents) {
				if ($dirToUserCachedFile !== '' && isset($current_user->ID) && $current_user->ID > 0) {
					if (!mkdir($localDirForCssFile) && !is_dir($localDirForCssFile)) {
						return array('uri_final_css_file' => '', 'local_final_css_file' => '');
					}
				}

				@file_put_contents($localFinalCssFile, $finalAssetsContents);
			}
		}

		return array(
			'uri_final_css_file'   => $uriToFinalCssFile,
			'local_final_css_file' => $localFinalCssFile
		);
	}

	/**
	 * @return array
	 */
	public function getCssCachedTransient()
	{
		// Only clean request URIs allowed
		if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
			return array();
		}

		$optionValue = get_transient($this->_transientCssName);

		if ($optionValue) {
			$optionValueArray = json_decode($optionValue, ARRAY_A);

			$uriToFinalCssFile = $optionValueArray['uri_to_final_css_file'];
			$linkHrefs         = $optionValueArray['link_hrefs'];

			if ($uriToFinalCssFile) {
				return array(
					'uri_final_css_file'   => $uriToFinalCssFile,
					'local_final_css_file' => WP_CONTENT_DIR . self::$relPathCssCacheDir . $uriToFinalCssFile,
					'link_hrefs'           => $linkHrefs
				);
			}
		}

		return array();
	}

	/**
	 * @param $uriToFinalCssFile
	 * @param array $linkHrefs
	 */
	public function setCssCachedTransient($uriToFinalCssFile, $linkHrefs)
	{
		// Only clean request URIs allowed
		if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
			return;
		}

		$optionValue = json_encode(
			array(
				'request_'              => $_SERVER['REQUEST_URI'],
				'uri_to_final_css_file' => $uriToFinalCssFile,
				'link_hrefs'            => $linkHrefs
			)
		);

		set_transient($this->_transientCssName, $optionValue, self::$_transientExpiresIn);
	}

	/**
	 *
	 */
	public function deleteCssCachedTransient()
	{
		// Only clean request URIs allowed
		if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
			return;
		}

		delete_transient($this->_transientCssName);
	}

	/**
	 * @param $cssContent
	 * @param $appendBefore
	 *
	 * @return mixed
	 */
	public function maybeFixCssBackgroundUrls($cssContent, $appendBefore)
	{
		$cssContent = str_replace(
			array('url("../', "url('../", 'url(../'),
			array('url("'.$appendBefore.'../', "url('".$appendBefore.'../', 'url('.$appendBefore.'../'),
			$cssContent
		);

		// Avoid Background URLs starting with "data" or "http" as they do not need to have a path updated
		preg_match_all('/url\((?![\'"]?(?:data|http):)[\'"]?([^\'"\)]*)[\'"]?\)/i', $cssContent, $matches, PREG_PATTERN_ORDER);

		// If it start with forward slash (/), it doesn't need fix, just skip it
		// Also skip ../ types as they were already processed
		$toSkipList = array("url('/", 'url("/', 'url(/');

		//$cssContent = "\n".print_r($matches, true)."\n"; // For DEV purposes only

		foreach ($matches[0] as $match) {
			$fullUrlMatch = trim($match);

			foreach ($toSkipList as $toSkip) {
				if (substr($fullUrlMatch, 0, strlen($toSkip)) === $toSkip) {
					continue 2; // doesn't need any fix, go to the next match
				}
			}

			// Go through all situations: with and without quotes, with traversal directory (e.g. ../../)
			$alteredMatch = str_replace(
				array('url("', "url('"),
				array('url("'.$appendBefore, "url('".$appendBefore),
				$fullUrlMatch
			);

			$alteredMatch = trim($alteredMatch);

			if (! in_array($fullUrlMatch{4}, array("'", '"', '/', '.'))) {
				$alteredMatch = str_replace('url(', 'url('.$appendBefore, $alteredMatch);
				$alteredMatch = str_replace(array('")', '\')'), ')', $alteredMatch);
			}

			// Finally, apply the changes
			$cssContent = str_replace($fullUrlMatch, $alteredMatch, $cssContent);

			// Bug fix
			$cssContent = str_replace(
				array($appendBefore.'"'.$appendBefore, $appendBefore."'".$appendBefore),
				$appendBefore,
				$cssContent
			);
		}

		return $cssContent;
	}

	/**
	 * @param $href
	 *
	 * @return bool|string
	 */
	public static function getLocalAssetPath($href)
	{
		/*
		 * Validate it first
		 */
		// Asset's Host
		$assetHost = strtolower(parse_url($href, PHP_URL_HOST));

		// First check the host name
		$siteUrl = get_option('siteurl');
		$siteUrlHost = strtolower(parse_url($siteUrl, PHP_URL_HOST));

		if ($assetHost !== $siteUrlHost) {
			return false;
		}

		$hrefRelPath = self::getHrefRelPath($href);

		if (strpos($hrefRelPath, '/') === 0) {
			$hrefRelPath = substr($hrefRelPath, 1);
		}

		$localAssetPath = ABSPATH . $hrefRelPath;

		//file_put_contents(WP_CONTENT_DIR . '/cache/asset-cleanup/css/data.log', $localAssetPath);

		if (strpos($localAssetPath, '?ver=') !== false) {
			list($localAssetPathAlt,) = explode('?ver=', $localAssetPath);
			$localAssetPath = $localAssetPathAlt;
		}

		if (strrchr($localAssetPath, '.') === '.css' && file_exists($localAssetPath)) {
			return $localAssetPath;
		}

		return false;
	}

	/**
	 * @param $href
	 *
	 * @return mixed
	 */
	public static function getHrefRelPath($href)
	{
		$parseUrl = parse_url($href);
		$hrefHost = $parseUrl['host'];

		// Sometimes host is different on Staging websites such as the ones from Siteground
		// e.g. staging1.domain.com and domain.com
		// We need to make sure that the URI path is fetched correctly based on the host value from the $href
		$siteDbUrl = get_option('siteurl');
		$parseDbSiteUrl = parse_url($siteDbUrl);

		$dbSiteUrlHost = $parseDbSiteUrl['host'];

		$finalBaseUrl = str_replace($dbSiteUrlHost, $hrefHost, $siteDbUrl);

		return str_replace($finalBaseUrl, '', $href);
	}

	/**
	 * @return bool
	 */
	public function doCssCombine()
	{
		// No CSS files are combined in the Dashboard
		// Always in the front-end view
		// Do not combine if there's a POST request as there could be assets loading conditionally
		// that might not be needed when the page is accessed without POST, making the final CSS file larger
		if (! empty($_POST) || array_key_exists('wpacu_no_css_combine', $_GET) || is_admin()) {
			return false; // Do not combine
		}

		$pluginSettings = Main::instance()->settings;

		if ($pluginSettings['test_mode']) {
			return false; // Do not combine anything if "Test Mode" is ON
		}

		if ($pluginSettings['combine_loaded_css'] === '') {
			return false; // Do not combine
		}

		if ($pluginSettings['combine_loaded_css'] === 'for_admin' && Menu::userCanManageAssets()) {
			return true; // Do combine
		}

		if ($pluginSettings['combine_loaded_css'] === 'for_all') {
			return true; // Do combine
		}

		// Finally, return false as none of the checks above matched
		return false;
	}

	/**
	 * @param bool $redirectAfter
	 */
	public function clearAllCacheTransients($redirectAfter = false)
	{
		if ($this->doNotClearAllCache()) {
			return;
		}

		global $wpdb;

		// First, select all and get the combined CSS file names in order to be deleted
		// As the files will be regenerated on page loads
		$transientNamePrefix = self::$transientCssNamePrefix;

		$sqlSelect = <<<SQL
SELECT option_name, option_value FROM `{$wpdb->options}` WHERE option_name LIKE '_transient_{$transientNamePrefix}%'
SQL;
		$sqlResults = $wpdb->get_results($sqlSelect, ARRAY_A);

		foreach ($sqlResults as $sqlResult) {
			// 53 = length of the following
			// _transient_ (11) + self::$transientCssNamePrefix + MD5 value (always 32)
			$optionNameValidLength = 11 + strlen(self::$transientCssNamePrefix) + 32;

			// nothing is left by chance to make sure the right transients gets deleted
			if (strlen($sqlResult['option_name']) === $optionNameValidLength) {
				delete_transient( str_replace( '_transient_', '', $sqlResult['option_name'] ) );
				// @TODO: Remove cached CSS files after a while making sure no caching system still loads the old file
				//$this->removeCachedCssFile( $sqlResult['option_value'] );
			}
		}

		if ( $redirectAfter && wp_get_referer() ) {
			wp_safe_redirect( wp_get_referer() );
		}
	}

	/**
	 * Prevent clear cache function in the following situations
	 *
	 * @return bool
	 */
	public function doNotClearAllCache()
	{
		// WooCommerce GET or AJAX call
		if (array_key_exists('wc-ajax', $_GET) && $_GET['wc-ajax']) {
			return true;
		}

		if (defined('WC_DOING_AJAX') && WC_DOING_AJAX === true) {
			return true;
		}

		return false;
	}

	/**
	 * Triggers in the front-end view only for the current viewed page
	 */
	/*
	public function clearCssCacheFile()
	{
		$optionValue = get_transient($this->transientCssName);

		if ($optionValue) {
			$this->removeCachedCssFile($optionValue);
		}
	}
	*/

	/**
	 * @param $optionValue
	 */
	/*
	public function removeCachedCssFile($optionValue)
	{
		$optionValueArray  = json_decode($optionValue, ARRAY_A);
		$uriToFinalCssFile = $optionValueArray['uri_to_final_css_file'];

		$fullLocalPathToCachedCssFile = WP_CONTENT_DIR . self::$relPathCssCacheDir . $uriToFinalCssFile;

		// Check the extension to make sure a CSS file is in fact deleted from the cache
		if (file_exists($fullLocalPathToCachedCssFile) && strrchr($uriToFinalCssFile, '.') === '.css') {
			@unlink($fullLocalPathToCachedCssFile);
		}
	}
	*/
}
