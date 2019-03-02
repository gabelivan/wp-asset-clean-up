<?php
namespace WpAssetCleanUp\OptimiseAssets;

use WpAssetCleanUp\Misc;
use WpAssetCleanUp\Plugin;
use WpAssetCleanUp\Tools;

/**
 * Class OptimizeCommon
 * @package WpAssetCleanUp
 */
class OptimizeCommon
{
	/**
	 * @var string
	 */
	public static $relPathPluginCacheDir = '/cache/asset-cleanup/'; // keep forward slash at the end

	/**
	 *
	 */
	public function init()
	{
		add_action('switch_theme',               array($this, 'clearAllCache'));
		add_action('after_switch_theme',         array($this, 'clearAllCache'));

		// Is WP Rocket's page cache cleared? Clear Asset CleanUp's CSS cache files too
		if (array_key_exists('action', $_GET) && $_GET['action'] === 'purge_cache') {
			add_action( 'before_rocket_clean_domain', array( $this, 'clearAllCache' ) );
		}

		add_action('admin_post_assetcleanup_clear_assets_cache', function() {
			self::clearAllCache(true);
		});
	}

	/**
	 * @param $htmlSource
	 *
	 * @return string|string[]|null
	 */
	public static function cleanerHtmlSource($htmlSource)
	{
		// Removes HTML comments including MSIE conditional ones as they are left intact
		// and not combined with other JavaScript files in case the method is called from OptimizeJs.php
		return preg_replace('/<!--(.|\s)*?-->/', '', $htmlSource);
	}

	/**
	 * Is this a regular WordPress page (not feed, REST API etc.)?
	 * If not, do not proceed with any CSS/JS combine
	 *
	 * @return bool
	 */
	public static function doCombineIsRegularPage()
	{
		// In particular situations, do not process this
		if (   strpos($_SERVER['REQUEST_URI'], '/wp-content/plugins/') !== false
		    && strpos($_SERVER['REQUEST_URI'], '/wp-content/themes/')  !== false) {
			return false;
		}

		if (Misc::endsWith($_SERVER['REQUEST_URI'], '/comments/feed/')) {
			return false;
		}

		if (str_replace('//', '/', site_url().'/feed/') === $_SERVER['REQUEST_URI']) {
			return false;
		}

		if (is_feed()) { // any kind of feed page
			return false;
		}

		return true;
	}

	/**
	 * @param $filesSources
	 * @param $htmlSource
	 * @param $assetType
	 *
	 * @return mixed
	 */
	public static function stripJustCombinedFileTags($filesSources, $htmlSource, $assetType = 'css')
	{
		if ($assetType === 'css') {
			$tagName       = 'link';
			$sourceAttr    = 'href';
			$regExpPattern = '#<link[^>]*stylesheet[^>]*(>)#Usmi';
		} else {
			return $htmlSource;
		}

		//removeIf(development)
			/*
			elseif ($assetType === 'js')
			{
				$tagName       = 'script';
				$sourceAttr    = 'src';
				$regExpPattern = '/\<script(.*?)?\>(.|\\n)*?\<\/script\>/i';
			} else {
				return $htmlSource;
			}
			*/
		//endRemoveIf(development)

		preg_match_all($regExpPattern, $htmlSource, $matchesSourcesFromTags, PREG_SET_ORDER);

		//removeIf(development)
			//return print_r($matchesSourcesFromTags, true);
		//endRemoveIf(development)

		$linkTagsStripped = 0;

		foreach ($matchesSourcesFromTags as $matchSourceFromTag) {
			$matchedSourceFromTag = trim($matchSourceFromTag[0]);

			//removeIf(development)
				//if (! preg_match('/\<script(|\s+.*?)src=(|\s+|\'|")/i', $matchedSourceFromTag)) { continue; }
				//return print_r($matchesSourcesFromTags, true);
				//return $matchedSourceFromTag;
				//return print_r($filesSources, true);
			//endRemoveIf(development)

			$domTag = new \DOMDocument();
			$domTag->loadHTML($matchedSourceFromTag);

			foreach ($domTag->getElementsByTagName( $tagName ) as $tagObject) {
				if (! $tagObject->hasAttributes()) {
					continue;
				}

				foreach ($tagObject->attributes as $tagAttrs) {
					if ($tagAttrs->nodeName === $sourceAttr && in_array($tagAttrs->nodeValue, $filesSources)) {
						$replaceWith = '';

						//removeIf(development)
							// This is a signature in order to "tell" the script that the final combined CSS file will be added after the last script
							// and not before </html> tag so it will load before other possible inline JS code
							//if ($assetType === 'js' && $tagAttrs->nodeValue === end($filesSources)) {
							//	$replaceWith = self::$combinedJsFileBeforeReplace;
							//}
						//endRemoveIf(development)

						$htmlSourceBeforeLinkTagReplacement = $htmlSource;
						$htmlSource = str_replace($matchedSourceFromTag, $replaceWith, $htmlSource);

						if ($htmlSource !== $htmlSourceBeforeLinkTagReplacement) {
							$linkTagsStripped++;
						}

						continue;
					}
				}
			}
		}

		if ($linkTagsStripped < 1) {
			return 'do_not_combine';
		}

		return $htmlSource;
	}

	/**
	 * @param $href
	 * @param $assetType
	 *
	 * @return bool|string
	 */
	public static function getLocalAssetPath($href, $assetType)
	{
		// Check the host name
		$siteDbUrl   = get_option('siteurl');
		$siteUrlHost = strtolower(parse_url($siteDbUrl, PHP_URL_HOST));

		if (strpos($href, '//') === 0) {
			list ($urlPrefix) = explode('//', $siteDbUrl);
			$href = $urlPrefix . $href;
		}

		$externalHostsList = array(
			'fonts.googleapis.com'
		);

		/*
		 * Validate it first
		 */
		$assetHost = strtolower(parse_url($href, PHP_URL_HOST));

		if (in_array($assetHost, $externalHostsList)) {
			return false;
		}

		// Different host name (most likely 3rd party one such as fonts.googleapis.com or an external CDN)
		// Do not add it to the combine list
		if ($assetHost !== $siteUrlHost) {
			return false;
		}

		$hrefRelPath = self::getHrefRelPath($href);

		if (strpos($hrefRelPath, '/') === 0) {
			$hrefRelPath = substr($hrefRelPath, 1);
		}

		$localAssetPath = ABSPATH . $hrefRelPath;

		//removeIf(development)
			//file_put_contents(WP_CONTENT_DIR . '/cache/asset-cleanup/'.$assetType.'/data.log', $localAssetPath);
		//endRemoveIf(development)

		if (strpos($localAssetPath, '?ver=') !== false) {
			list($localAssetPathAlt,) = explode('?ver=', $localAssetPath);
			$localAssetPath = $localAssetPathAlt;
		}

		// Not using "?ver="
		if (strpos($localAssetPath, '.'.$assetType.'?') !== false) {
			list($localAssetPathAlt,) = explode('.'.$assetType.'?', $localAssetPath);
			$localAssetPath = $localAssetPathAlt.'.'.$assetType;
		}

		//removeIf(development)
			// TODO: Consider combining files that are like style.php?parameter_here
			// as style.php can't be just read via file_get_contents(), it needs to be accessed differently
		//endRemoveIf(development)

		if (strrchr($localAssetPath, '.') === '.'.$assetType && file_exists($localAssetPath)) {
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
		$hrefHost = isset($parseUrl['host']) ? $parseUrl['host'] : false;

		if (! $hrefHost) {
			return $href;
		}

		// Sometimes host is different on Staging websites such as the ones from Siteground
		// e.g. staging1.domain.com and domain.com
		// We need to make sure that the URI path is fetched correctly based on the host value from the $href
		$siteDbUrl      = get_option('siteurl');
		$parseDbSiteUrl = parse_url($siteDbUrl);

		$dbSiteUrlHost  = $parseDbSiteUrl['host'];

		$finalBaseUrl   = str_replace($dbSiteUrlHost, $hrefHost, $siteDbUrl);

		return str_replace($finalBaseUrl, '', $href);
	}

	/**
	 * @param $jsonStorageFile
	 * @param $relPathAssetCacheDir
	 * @param $assetType
	 *
	 * @return array|mixed|object
	 */
	public static function getAssetCachedData($jsonStorageFile, $relPathAssetCacheDir, $assetType)
	{
		// Only clean request URIs allowed
		if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
			list($requestUri) = explode('?', $_SERVER['REQUEST_URI']);
		} else {
			$requestUri = $_SERVER['REQUEST_URI'];
		}

		$requestUriPart = $requestUri;

		if ($requestUri === '/' || is_404()) {
			$requestUriPart = '';
		}

		$dirToFilename = WP_CONTENT_DIR . dirname($relPathAssetCacheDir).'/_storage/'
		                 .parse_url(site_url(), PHP_URL_HOST).
		                 $requestUriPart.'/';

		$dirToFilename = str_replace('//', '/', $dirToFilename);

		$assetsFile = $dirToFilename . self::filterStorageFileName($jsonStorageFile);

		if (! file_exists($assetsFile)) {
			return array();
		}

		if ($assetType === 'css') {
			$cachedAssetsFileExpiresIn = OptimizeCss::$cachedCssAssetsFileExpiresIn;
		} elseif ($assetType === 'js') {
			$cachedAssetsFileExpiresIn = OptimizeJs::$cachedJsAssetsFileExpiresIn;
		} else {
			return array();
		}

		// Delete cached file after it expired as it will be regenerated
		if (filemtime($assetsFile) < (time() - 1 * $cachedAssetsFileExpiresIn)) {
			self::clearAssetCachedData($jsonStorageFile);
			return array();
		}

		$optionValue = @file_get_contents($assetsFile);

		if ($optionValue) {
			$optionValueArray = @json_decode($optionValue, ARRAY_A);

			if ($assetType === 'css' && ! empty( $optionValueArray) && (isset($optionValueArray['head']['link_hrefs']) || isset($optionValueArray['body']['link_hrefs']))) {
				//removeIf(development)
				/*
				$uriToFinalCssFile = $optionValueArray['uri_to_final_css_file'];
				$linkHrefs         = $optionValueArray['link_hrefs'];

				if ($uriToFinalCssFile) {
					return array(
						'uri_final_css_file'   => $uriToFinalCssFile,
						'local_final_css_file' => WP_CONTENT_DIR . $relPathAssetCacheDir . $uriToFinalCssFile,
						'link_hrefs'           => array_map( function ( $linkHref ) {
							return str_replace( '{site_url}', site_url(), $linkHref );
						}, $linkHrefs )
					);
				}
				*/
				//endRemoveIf(development)
				return $optionValueArray;
			}

			if ($assetType === 'js' && ! empty($optionValueArray)) {
				return $optionValueArray;
			}
		}

		// File exists, but it's invalid or outdated; Delete it as it has to be re-generated
		self::clearAssetCachedData($jsonStorageFile);
		return array();
	}

	/**
	 * @param $jsonStorageFile
	 * @param $relPathAssetCacheDir
	 * @param $list
	 */
	public static function setAssetCachedData($jsonStorageFile, $relPathAssetCacheDir, $list)
	{
		// Only clean request URIs allowed
		if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
			list($requestUri) = explode('?', $_SERVER['REQUEST_URI']);
		} else {
			$requestUri = $_SERVER['REQUEST_URI'];
		}

		$requestUriPart = $requestUri;

		if ($requestUri === '/' || is_404()) {
			$requestUriPart = '';
		}

		//removeIf(development)
			// TODO: Check if the directories created via mkdir() below, should have been created in the first place
			// sometimes, specific requests in the REQUEST URI shouldn't have any JavaScript files in their source code
		//endRemoveIf(development)

		$dirToFilename = WP_CONTENT_DIR . dirname($relPathAssetCacheDir).'/_storage/'
		                 .parse_url(site_url(), PHP_URL_HOST).
		                 $requestUriPart.'/';

		$dirToFilename = str_replace('//', '/', $dirToFilename);

		if (! mkdir($dirToFilename, 0755, true) && ! is_dir($dirToFilename)) {
			return;
		}

		$assetsFile = $dirToFilename . self::filterStorageFileName($jsonStorageFile);

		// CSS/JS JSON FILE DATA
		//removeIf(development)
			/*
			if (isset($list['uri_to_final_css_file'])) {
				$assetsValue = json_encode(
					array(
						'uri_to_final_css_file' => $list['uri_to_final_css_file'],
						'link_hrefs'            => 	array_map( function ( $linkHref ) {
							return str_replace( site_url(), '{site_url}', $linkHref );
						}, $list['link_hrefs'] )
					)
				);
			}
			*/
		//endRemoveIf(development)
		$assetsValue = $list;

		@file_put_contents($assetsFile, $assetsValue);
	}

	/**
	 * @param $jsonStorageFile
	 */
	public static function clearAssetCachedData($jsonStorageFile)
	{
		// Only clean request URIs allowed
		if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
			list($requestUri) = explode('?', $_SERVER['REQUEST_URI']);
		} else {
			$requestUri = $_SERVER['REQUEST_URI'];
		}

		$requestUriPart = $requestUri;

		if ($requestUri === '/' || is_404()) {
			$requestUriPart = '';
		}

		$dirToFilename = WP_CONTENT_DIR . self::$relPathPluginCacheDir . '_storage/'
		                 . parse_url(site_url(), PHP_URL_HOST) .
		                 $requestUriPart . '/';

		$assetsFile = $dirToFilename . self::filterStorageFileName($jsonStorageFile);

		@unlink($assetsFile);
	}

	/**
	 * Clears all CSS & JS cache
	 *
	 * @param bool $redirectAfter
	 * @param bool $keepAssetFiles
	 *
	 *  $keepAssetFiles is kept to "true" as default
	 *  there could be cache plugins still having cached pages that load specific merged files,
	 *  to avoid breaking the layout/functionality
	 */
	public static function clearAllCache($redirectAfter = false, $keepAssetFiles = true)
	{
		if (self::doNotClearAllCache()) {
			return;
		}

		/*
		 * STEP 1: Clear all .json, maybe .css & .js files that are related to "Combine CSS/JS files" feature
		 */
		$fileExtToRemove = array('.json');

		// Also delete .css & .js
		if (! $keepAssetFiles) {
			$fileExtToRemove[] = '.css';
			$fileExtToRemove[] = '.js';
		}

		$assetCleanUpCacheDir = WP_CONTENT_DIR . self::$relPathPluginCacheDir;
		$storageDir           = $assetCleanUpCacheDir.'_storage';

		if (is_dir($assetCleanUpCacheDir)) {
			$dirItems = new \RecursiveDirectoryIterator( $assetCleanUpCacheDir, \RecursiveDirectoryIterator::SKIP_DOTS );

			$storageEmptyDirs = array();

			foreach ( new \RecursiveIteratorIterator( $dirItems, \RecursiveIteratorIterator::SELF_FIRST ) as $item ) {
				$fileBaseName = strrchr( $item, '/' );

				if ( is_file( $item ) && in_array( strrchr( $fileBaseName, '.' ), $fileExtToRemove ) ) {
					@unlink( $item );
				} elseif ( strpos( $item, $storageDir ) !== false && $item != $storageDir ) {
					$storageEmptyDirs[] = $item;
				}
			}

			foreach ( array_reverse( $storageEmptyDirs ) as $storageEmptyDir ) {
				@rmdir( $storageEmptyDir );
			}
		}

		/*
		 * STEP 2: Remove all transients related to the Minify CSS/JS files feature
		 */
		$toolsClass = new Tools();
		$toolsClass->clearAllCacheTransients();

		// Make sure all the caching files/folders are there in case the plugin was upgraded
		Plugin::createCacheFoldersFiles(array('css', 'js'));

		//removeIf(development)
			// "WP Rocket" cache
			/*
			add_action('init', function() {
				if ( function_exists( 'rocket_clean_domain' ) ) {
					try {
						@rocket_clean_domain();
					} catch ( \Exception $e ) {}
				}
			});
			*/
		//endRemoveIf(development)

		if ( $redirectAfter && wp_get_referer() ) {
			wp_safe_redirect( wp_get_referer() );
		}
	}

	/**
	 * Prevent clear cache function in the following situations
	 *
	 * @return bool
	 */
	public static function doNotClearAllCache()
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
	 * @param $fileName
	 *
	 * @return mixed
	 */
	public static function filterStorageFileName($fileName)
	{
		$filterString = '';

		if (is_404()) {
			$filterString = '-404-not-found';
		}

		$current_user = wp_get_current_user();

		if (isset($current_user->ID) && $current_user->ID > 0) {
			$fileName = str_replace(
				'{maybe-extra-info}',
				$filterString.'-logged-in-'.$current_user->ID,
				$fileName
			);
		} else {
			// Just clear {maybe-extra-info}
			$fileName = str_replace('{maybe-extra-info}', $filterString, $fileName);
		}

		return $fileName;
	}

	/**
	 * URLs with query strings are not loading Optimised Assets (e.g. combine CSS files into one file)
	 * However, there are exceptions such as the ones below (preview, debugging purposes)
	 *
	 * @return bool
	 */
	public static function loadOptimizedAssetsIfQueryStrings()
	{
		$isPreview = (isset($_GET['preview_id'], $_GET['preview_nonce'], $_GET['preview']) || isset($_GET['preview']));
		$isQueryStringDebug = isset($_GET['wpacu_no_css_minify']) || isset($_GET['wpacu_no_js_minify']) || isset($_GET['wpacu_no_css_combine']) || isset($_GET['wpacu_no_js_combine']);

		return ($isPreview || $isQueryStringDebug);
	}
}
