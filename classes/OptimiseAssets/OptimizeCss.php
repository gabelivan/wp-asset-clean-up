<?php
namespace WpAssetCleanUp\OptimiseAssets;

use WpAssetCleanUp\Main;
use WpAssetCleanUp\Menu;
use WpAssetCleanUp\MetaBoxes;

/**
 * Class OptimizeCss
 * @package WpAssetCleanUp
 */
class OptimizeCss
{
	/**
	 * @var string
	 */
	public static $relPathCssCacheDir = '/cache/asset-cleanup/css/'; // keep forward slash at the end

	/**
	 * @var float|int
	 */
	public static $cachedCssAssetsFileExpiresIn = 28800; // 8 hours in seconds (60 * 60 * 8)

	/**
	 * @var string
	 */
	public $jsonStorageFile = 'css-combined{maybe-extra-info}.json';

	/**
	 *
	 */
	public function init()
	{
		add_action('wp_loaded', function() {
			if (is_admin()) { // don't apply any changes if not in the front-end view (e.g. Dashboard view)
				return;
			}

			ob_start(function ($htmlSource) {
				// Do not do any optimization if "Test Mode" is Enabled
				//removeIf(development)
					// @TODO: Make sure to reduce the code below to avoid redundancy as "Test Mode" is also checked in doCssCombine() method
				//endRemoveIf(development)
				if (! Menu::userCanManageAssets() && Main::instance()->settings['test_mode']) {
					return $htmlSource;
				}

				// There has to be at least one "<link", otherwise, it could be a feed request or something similar (not page, post, homepage etc.)
				if (stripos($htmlSource, '<link') === false) {
					return $htmlSource;
				}

				if (Main::instance()->settings['minify_loaded_css']) {
					// 'wpacu_css_minify_list' caching list is also checked; if it's empty, no minification is made
					$htmlSource = MinifyCss::updateHtmlSourceOriginalToMinCss( $htmlSource );
				}

				if ( array_key_exists('wpacu_no_css_combine', $_GET) || // not on query string request (debugging purposes)
					! $this->doCssCombine() ) {
					return $htmlSource;
				}

				// If "Do not combine CSS on this page" is checked in "Asset CleanUp: Options" side meta box
				// Works for posts, pages and custom post types
				if (defined('WPACU_CURRENT_PAGE_ID') && WPACU_CURRENT_PAGE_ID > 0) {
					$pageOptions = MetaBoxes::getPageOptions( WPACU_CURRENT_PAGE_ID );

					if ( isset( $pageOptions['no_css_optimize'] ) && $pageOptions['no_css_optimize'] ) {
						return $htmlSource;
					}
				}

				$useDom = function_exists('libxml_use_internal_errors') && function_exists('libxml_clear_errors') && class_exists('DOMDocument');

				if (! $useDom) {
					return $htmlSource;
				}

				// Speed up processing by getting the already existing final CSS file URI
				// This will avoid parsing the HTML DOM and determine the combined URI paths for all the CSS files
				$storageJsonContents = OptimizeCommon::getAssetCachedData($this->jsonStorageFile, self::$relPathCssCacheDir, 'css');

				// $uriToFinalCssFile will always be relative ONLY within WP_CONTENT_DIR . self::$relPathCssCacheDir
				// which is usually "wp-content/cache/asset-cleanup/css/"

				//removeIf(development)
					//return print_r($storageJsonContents, true);
				//endRemoveIf(development)

				//removeIf(development)
				$skipCache = true;
				//endRemoveIf(development)

				if (//removeIf(development)
					$skipCache ||
					//endRemoveIf(development)
					empty($storageJsonContents)) {
					$storageJsonContentsToSave = array();

					/*
					 * NO CACHING? Parse the DOM
					*/
					// Nothing in the database records or the retrieved cached file does not exist?
					OptimizeCommon::clearAssetCachedData( $this->jsonStorageFile );

					// Fetch the DOM, and then set a new transient
					$documentForCSS = new \DOMDocument();
					$documentForCSS->loadHTML( $htmlSource );
					libxml_use_internal_errors( true );

					$storageJsonContents = array();

					foreach ( array( 'head', 'body' ) as $docLocationTag ) {
						$combinedUriPaths = $hrefUriNotCombinableList = $localAssetsPaths = $linkHrefs = array();

						$docLocationElements = $documentForCSS->getElementsByTagName( $docLocationTag )->item( 0 );
						$linkTags            = $docLocationElements->getElementsByTagName( 'link' );

						//removeIf(development)
						//if ($docLocationTag === 'body') {
							//return print_r( $linkTags, true );
						//}
						//endRemoveIf(development)

						if ( $linkTags === null ) {
							continue;
						}

						foreach ( $linkTags as $tagObject ) {
							if ( ! $tagObject->hasAttributes() ) {
								continue;
							}

							$getHref = $href = false;

							$linkAttributes = array();

							foreach ( $tagObject->attributes as $attrObj ) {
								$linkAttributes[ $attrObj->nodeName ] = $attrObj->nodeValue;

								// Only rel="stylesheet" (with no rel="preload" associated with it) gets prepared for combining as links with rel="preload" (if any) are never combined into a standard render-blocking CSS file
								// rel="preload" is there for a reason to make sure the CSS code is made available earlier prior to the one from rel="stylesheet" which is render-blocking
								if ( $attrObj->nodeName === 'rel' && $attrObj->nodeValue === 'stylesheet' ) {
									$getHref = true;
								}

								if ( $getHref && $attrObj->nodeName === 'href' ) {
									// Make sure that tag value is checked and it's matched against the value from the HTML source code
									//$htmlSource .= $attrObj->nodeValue."\n";
									$href = (string) $attrObj->nodeValue;

									$localAssetPath = OptimizeCommon::getLocalAssetPath( $href, 'css' );

									// It will skip external stylesheets (from a different domain)
									if ( $localAssetPath ) {
										$combinedUriPaths[]        = OptimizeCommon::getHrefRelPath( $href );
										$localAssetsPaths[ $href ] = $localAssetPath;
										$linkHrefs[]               = $href;
									}
								}

								if ( ! $getHref ) {
									continue;
								}

								$cssNotCombinable = false;

								// 1) Check if there is any rel="preload" connected to the rel="stylesheet"
								//    making sure the file is not added to the final CSS combined file

								// 2) Only combine media "all", "screen" and the ones with no media
								//    Do not combine media='only screen and (max-width: 768px)' etc.
								if ( isset( $linkAttributes['rel'] ) && $linkAttributes['rel'] === 'preload' ) {
									$cssNotCombinable = true;
								}

								if ( array_key_exists( 'media',
										$linkAttributes ) && ! in_array( $linkAttributes['media'],
										array( 'all', 'screen' ) ) ) {
									$cssNotCombinable = true;
								}

								if ( $this->skipCombine( $linkAttributes['href'] ) ) {
									$cssNotCombinable = true;
								}

								if ( $cssNotCombinable ) {
									$hrefUriNotCombinableList[] = OptimizeCommon::getHrefRelPath( $href );
								}
							}

							// Any rel="preload" or media="print" found? Remove the stylesheet from the combination
							if ( ! empty( $hrefUriNotCombinableList ) ) {
								foreach ( $hrefUriNotCombinableList as $hrefUriNotCombinable ) {
									if ( in_array( $hrefUriNotCombinable, $combinedUriPaths ) ) {
										$linkHrefUriKey = array_search( $hrefUriNotCombinable, $combinedUriPaths );
										unset( $combinedUriPaths[ $linkHrefUriKey ] );

										foreach ( array_keys( $localAssetsPaths ) as $localAssetPathKey ) {
											if ( substr( $localAssetPathKey, - strlen( $hrefUriNotCombinable ) ) === $hrefUriNotCombinable ) {
												unset( $localAssetsPaths[ $localAssetPathKey ] );
											}
										}

										foreach ( $linkHrefs as $linkHrefKey => $linkHref ) {
											if ( substr( $linkHref, - strlen( $hrefUriNotCombinable ) ) === $hrefUriNotCombinable ) {
												unset( $linkHrefs[ $linkHrefKey ] );
											}
										}
									}
								}
							}
						}

						//removeIf(development)
							/*
							if ($docLocationTag === 'body') {
								return print_r($linkHrefs, true);
							}
							*/
						//endRemoveIf(development)

						// No Link Tags? Continue
						if ( empty( $linkHrefs ) ) {
							continue;
						}

						$maybeDoCssCombine = $this->maybeDoCssCombine( sha1( implode( '', $combinedUriPaths ) ),
							$localAssetsPaths, $linkHrefs );

						// Local path to combined CSS file
						$localFinalCssFile = $maybeDoCssCombine['local_final_css_file'];

						// URI (e.g. /wp-content/cache/asset-cleanup/[file-name-here.css]) to the combined CSS file
						$uriToFinalCssFile = $maybeDoCssCombine['uri_final_css_file'];

						// Any link hrefs removed perhaps if the file wasn't combined?
						$linkHrefs = $maybeDoCssCombine['link_hrefs'];

						if ( file_exists( $localFinalCssFile ) ) {
							$storageJsonContents[$docLocationTag] = array(
								'uri_to_final_css_file' => $uriToFinalCssFile,
								'link_hrefs'            => array_map( function ( $href ) {
									return str_replace( '{site_url}', site_url(), $href );
								}, $linkHrefs )
							);

							$storageJsonContentsToSave[$docLocationTag] = array(
								'uri_to_final_css_file' => $uriToFinalCssFile,
								'link_hrefs'            => array_map( function ( $href ) {
									return str_replace( site_url(), '{site_url}', $href );
								}, $linkHrefs )
							);
						}
					}

					OptimizeCommon::setAssetCachedData(
						$this->jsonStorageFile,
						self::$relPathCssCacheDir,
						json_encode($storageJsonContentsToSave)
					);
				}

				if ( ! empty($storageJsonContents) ) {
					foreach ($storageJsonContents as $locationTag => $storageJsonContent) {
						$storageJsonContent['link_hrefs'] = array_map( function ( $href ) {
							return str_replace( '{site_url}', site_url(), $href );
						}, $storageJsonContent['link_hrefs'] );

						$finalTagUrl = WP_CONTENT_URL . self::$relPathCssCacheDir . $storageJsonContent['uri_to_final_css_file'];

						$finalCssTag = <<<HTML
<link id='asset-cleanup-combined-css-{$locationTag}' rel='stylesheet' href='{$finalTagUrl}' type='text/css' media='all' />
HTML;

						$htmlSourceBeforeAnyLinkTagReplacement = $htmlSource;

						// Detect first LINK tag from the <$locationTag> and replace it with the final combined LINK tag
						$firstLinkTag = $this->getFirstLinkTag($storageJsonContent['link_hrefs'][0], $htmlSource);

						if ($firstLinkTag) {
							$htmlSource = str_replace( $firstLinkTag, $finalCssTag, $htmlSource );
						}

						if ($htmlSource !== $htmlSourceBeforeAnyLinkTagReplacement) {
							$htmlSource = OptimizeCommon::stripJustCombinedFileTags( $storageJsonContent['link_hrefs'], $htmlSource, 'css' ); // Strip the combined files to avoid duplicate code

							// There should be at least two replacements made
							if ( $htmlSource === 'do_not_combine' ) {
								$htmlSource = $htmlSourceBeforeAnyLinkTagReplacement;
							}
						}
					}
				}

				return $htmlSource;
			});
		}, 1);
	}

	/**
	 * @param $firstLinkHref
	 * @param $htmlSource
	 *
	 * @return string
	 */
	public function getFirstLinkTag($firstLinkHref, $htmlSource)
	{
		$regExpPattern = '#<link[^>]*stylesheet[^>]*(>)#Usmi';

		preg_match_all($regExpPattern, $htmlSource, $matches);
		//removeIf(development)
		//return $matches[0];
		//endRemoveIf(development)

		foreach ($matches[0] as $matchTag) {
			if (strpos($matchTag, $firstLinkHref) !== false) {
				return trim($matchTag);
			}
		}

		return '';
	}

	/**
	 * @param $shaOneCombinedUriPaths
	 * @param $localAssetsPaths
	 * @param $linkHrefs
	 *
	 * @return array
	 */
	public function maybeDoCssCombine($shaOneCombinedUriPaths, $localAssetsPaths, $linkHrefs)
	{
		$current_user = wp_get_current_user();
		$dirToUserCachedFile = ((isset($current_user->ID) && $current_user->ID > 0) ? 'logged-in/'.$current_user->ID.'/' : '');

		$uriToFinalCssFile = $dirToUserCachedFile . $shaOneCombinedUriPaths . '.css';
		$localFinalCssFile = WP_CONTENT_DIR . self::$relPathCssCacheDir . $uriToFinalCssFile;

		$localDirForCssFile = WP_CONTENT_DIR . self::$relPathCssCacheDir . $dirToUserCachedFile;

		// Only combine if $shaOneCombinedUriPaths.css does not exist
		// If "?ver" value changes on any of the assets or the asset list changes in any way
		// then $shaOneCombinedUriPaths will change too and a new CSS file will be generated and loaded

		$skipIfFileExists = true;

		//removeIf(development)
			// || defined('WPACU_DEV_ALWAYS_GENERATE_COMBINED_CSS'
		//endRemoveIf(development)
		if ($skipIfFileExists || ! file_exists($localFinalCssFile)) {
			// Change $assetsContents as paths to fonts and images that are relative (e.g. ../, ../../) have to be updated
			$finalAssetsContents = '';

			foreach ($localAssetsPaths as $assetHref => $localAssetsPath) {
				$posLastSlash   = strrpos($assetHref, '/');
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
					// Do not combine it if it contains "@import"
					if (stripos($assetContent, '@import') !== false) {
						unset($localAssetsPaths[$assetHref]);
						$linkHrefKey = array_search($assetHref, $linkHrefs);
						unset($linkHrefs[$linkHrefKey]);
						continue;
					}

					$finalAssetsContents .= self::maybeFixCssBackgroundUrls($assetContent, $pathToAssetDir . '/') . "\n\n";
				}
			}

			$finalAssetsContents = trim($finalAssetsContents);

			if ($finalAssetsContents) {
				if ($dirToUserCachedFile !== '' && isset($current_user->ID) && $current_user->ID > 0
				    && !mkdir($localDirForCssFile) && !is_dir($localDirForCssFile)) {
						return array('uri_final_css_file' => '', 'local_final_css_file' => '');
				}

				@file_put_contents($localFinalCssFile, $finalAssetsContents);
			}
		}

		return array(
			'uri_final_css_file'   => $uriToFinalCssFile,
			'local_final_css_file' => $localFinalCssFile,
			'link_hrefs'           => $linkHrefs
		);
	}

	/**
	 * @param $cssContent
	 * @param $appendBefore
	 *
	 * @return mixed
	 */
	public static function maybeFixCssBackgroundUrls($cssContent, $appendBefore)
	{
		$cssContent = str_replace(
			array('url("../', "url('../", 'url(../'),
			array('url("'.$appendBefore.'../', "url('".$appendBefore.'../', 'url('.$appendBefore.'../'),
			$cssContent
		);

		// Avoid Background URLs starting with "data" or "http" as they do not need to have a path updated
		preg_match_all('/url\((?![\'"]?(?:data|http):)[\'"]?([^\'"\)]*)[\'"]?\)/i', $cssContent, $matches);

		// If it start with forward slash (/), it doesn't need fix, just skip it
		// Also skip ../ types as they were already processed
		$toSkipList = array("url('/", 'url("/', 'url(/');

		//removeIf(development)
			//$cssContent = "\n".print_r($matches, true)."\n"; // For DEV purposes only
		//endRemoveIf(development)

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

			// Bug Fix 2
			$cssContent = str_replace($appendBefore . 'http', 'http', $cssContent);
			$cssContent = str_replace($appendBefore . '//', '//', $cssContent);
		}

		return $cssContent;
	}

	/**
	 * @param $href
	 *
	 * @return bool
	 */
	public function skipCombine($href)
	{
		$regExps = array();

		if (Main::instance()->settings['combine_loaded_css_exceptions'] !== '') {
			$loadedCssExceptionsPatterns = trim(Main::instance()->settings['combine_loaded_css_exceptions']);

			if (strpos($loadedCssExceptionsPatterns, "\n")) {
				// Multiple values (one per line)
				foreach (explode("\n", $loadedCssExceptionsPatterns) as $loadedCssExceptionPattern) {
					$regExps[] = '#'.trim($loadedCssExceptionPattern).'#';
				}
			} else {
				// Only one value?
				$regExps[] = '#'.trim($loadedCssExceptionsPatterns).'#';
			}
		}

		// No exceptions set? Do not skip combination
		if (empty($regExps)) {
			return false;
		}

		foreach ($regExps as $regExp) {
			if ( preg_match( $regExp, $href ) ) {
				// Skip combination
				return true;
			}
		}

		return false;
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
		if (! empty($_POST) || is_admin()) {
			return false; // Do not combine
		}

		// Only clean request URIs allowed
		if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
			// Exceptions
			if (! OptimizeCommon::loadOptimizedAssetsIfQueryStrings()) {
				return false;
			}
		}

		if (! OptimizeCommon::doCombineIsRegularPage()) {
			return false;
		}

		$pluginSettings = Main::instance()->settings;

		if ($pluginSettings['test_mode'] && ! Menu::userCanManageAssets()) {
			return false; // Do not combine anything if "Test Mode" is ON and the user is in guest mode (not logged-in)
		}

		if ($pluginSettings['combine_loaded_css'] === '') {
			return false; // Do not combine
		}

		if ( ($pluginSettings['combine_loaded_css'] === 'for_admin'
		      || $pluginSettings['combine_loaded_css_for_admin_only'] == 1)
		     && Menu::userCanManageAssets()) {
			return true; // Do combine
		}

		if ( $pluginSettings['combine_loaded_css_for_admin_only'] === ''
		     && in_array($pluginSettings['combine_loaded_css'], array('for_all', 1)) ) {
			return true; // Do combine
		}

		// Finally, return false as none of the checks above matched
		return false;
	}

	//removeIf(development)
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
	//endRemoveIf(development)
}
