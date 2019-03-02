<?php
namespace WpAssetCleanUp\OptimiseAssets;

use WpAssetCleanUp\Main;
use WpAssetCleanUp\Menu;
use WpAssetCleanUp\MetaBoxes;
use WpAssetCleanUp\Misc;

/**
 * Class CombineJs
 * @package WpAssetCleanUp
 */
class OptimizeJs
{
	/**
	 * @var string
	 */
	public static $relPathJsCacheDir = '/cache/asset-cleanup/js/'; // keep trailing slash at the end

	/**
	 * @var float|int
	 */
	public static $cachedJsAssetsFileExpiresIn = 28800; // 8 hours in seconds (60 * 60 * 8)

	/**
	 * @var string
	 */
	public $jsonStorageFile = 'js-combined{maybe-extra-info}.json';

	/**
	 *
	 */
	public function init()
	{
		add_action('wp_loaded', function() {
			if (is_admin()) { // don't apply any changes if not in the front-end view (e.g. Dashboard view)
				return;
			}

			ob_start(function($htmlSource) {
				// Do not do any optimization if "Test Mode" is Enabled
				//removeIf(development)
					// @TODO: Make sure to reduce the code below to avoid redundancy as "Test Mode" is also checked in doCssCombine() method
				//endRemoveIf(development)
				if (! Menu::userCanManageAssets() && Main::instance()->settings['test_mode']) {
					return $htmlSource;
				}

				// There has to be at least one "<script", otherwise, it could be a feed request or something similar (not page, post, homepage etc.)
				if (stripos($htmlSource, '<script') === false) {
					return $htmlSource;
				}

				/*
				 * #minifying
				 * STEP 2: Load minify-able caching list and replace the original source URLs with the new cached ones
				 */
				if (Main::instance()->settings['minify_loaded_js']) {
					// 'wpacu_js_minify_list' caching list is also checked; if it's empty, no minification is made
					$htmlSource = MinifyJs::updateHtmlSourceOriginalToMinJs( $htmlSource );
				}

				if ( array_key_exists('wpacu_no_js_combine', $_GET) || // not on query string request (debugging purposes)
					 ! $this->doJsCombine() ) {
					return $htmlSource;
				}

				// If "Do not combine CSS on this page" is checked in "Asset CleanUp Options" side meta box
				// Works for posts, pages and custom post types
				if (defined('WPACU_CURRENT_PAGE_ID') && WPACU_CURRENT_PAGE_ID > 0) {
					$pageOptions = MetaBoxes::getPageOptions( WPACU_CURRENT_PAGE_ID );

					if ( isset( $pageOptions['no_js_optimize'] ) && $pageOptions['no_js_optimize'] ) {
						return $htmlSource;
					}
				}

				$useDom = function_exists('libxml_use_internal_errors') && function_exists('libxml_clear_errors') && class_exists('DOMDocument');

				if (! $useDom) {
					return $htmlSource;
				}

				//removeIf(development)
					//$combineType = 'basic';
					//$combineLevel = Main::instance()->settings['combine_loaded_js_level'];
				//endRemoveIf(development)
				$combineLevel = 2;

				//removeIf(development)
					//$jsCachedFilesExists = false;
				//endRemoveIf(development)

				// Speed up processing by getting the already existing final CSS file URI
				// This will avoid parsing the HTML DOM and determine the combined URI paths for all the CSS files
				$finalCacheList = OptimizeCommon::getAssetCachedData($this->jsonStorageFile, self::$relPathJsCacheDir, 'js');

				// $uriToFinalJsFile will always be relative ONLY within WP_CONTENT_DIR . self::$relPathJsCacheDir
				// which is usually "wp-content/cache/asset-cleanup/js/"

				// "false" would make it avoid checking the cache and always use the DOM Parser / RegExp
				// for DEV purposes ONLY as it uses more resources
				//removeIf(development)
				$skipCache = true;
				//endRemoveIf(development)

				if (//removeIf(development)
					(isset($skipCache) && $skipCache) ||
					//endRemoveIf(development)
					empty($finalCacheList)) {
					/*
					 * NO CACHING TRANSIENT; Parse the DOM
					*/
					// Nothing in the database records or the retrieved cached file does not exist?
					OptimizeCommon::clearAssetCachedData($this->jsonStorageFile);

					$regExpPattern = '#<script[^>]*>.*?</script>#is';

					//removeIf(development)
						//$regExpPattern = '/<script\\b[^>]*>(.*?)<\\/script>/i';
						//$regExpPattern = '/\<script(.*?)?\>(.*?)<\/script\>/i';
						//$regExpPattern = '/\<script(\s+.*?)(|\s+.*?)src=(|\s+|\'|")(.*?)\>(.*?)<\/script\>/i';
					//endRemoveIf(development)

					preg_match_all($regExpPattern, OptimizeCommon::cleanerHtmlSource($htmlSource), $matchesSourcesFromTags, PREG_SET_ORDER);

					//removeIf(development)
						//return print_r($matchesSourcesFromTags, true);
					//endRemoveIf(development)

					// No <script> tag found? Do not continue
					if (empty($matchesSourcesFromTags)) {
						return $htmlSource;
					}

					if ($combineLevel === 2) {
						$matchesSourcesFromTags = $this->clearInlineScriptTags($matchesSourcesFromTags);
					}

					if (empty($matchesSourcesFromTags)) {
						return $htmlSource;
					}

					//removeIf(development)
						// after filtering
						//return print_r($matchesSourcesFromTags, true);
					//endRemoveIf(development)

					$combinableList = $bodyGroupIndexes = array();

					$groupIndex = 1;
					$jQueryAndMigrateGroup = 0;

					$jQueryGroupIndex = $loadsLocaljQuery = $loadsLocaljQueryMigrate = false;

					$lastScriptSrcFromHead = $this->lastScriptSrcFromHead($htmlSource);

					$reachedBody = false;

					// Only keep combinable JS files
					foreach ($matchesSourcesFromTags as $matchSourceFromTag) {
						//removeIf(development)
							//return print_r($matchSourceFromTag, true);
						//endRemoveIf(development)

						$matchedSourceFromTag = trim( $matchSourceFromTag[0] );

						$domTag = new \DOMDocument();
						$domTag->loadHTML($matchedSourceFromTag);

						$hasSrc = $src = false;

						foreach ($domTag->getElementsByTagName( 'script' ) as $tagObject) {
							if (! $tagObject->hasAttributes()) {
								continue;
							}

							//removeIf(development)
								//$scriptAttrs = array();
							//endRemoveIf(development)
							foreach ( $tagObject->attributes as $attrObj ) {
								//removeIf(development)
									//$hasSrc = (isset($scriptAttrs['src']) && $scriptAttrs['src']);
									//$scriptAttrs[ $attrObj->nodeName ] = $attrObj->nodeValue;
								//endRemoveIf(development)

								if ($attrObj->nodeName === 'src' && $attrObj->nodeValue) {
									$hasSrc = true;
									$src = (string) $attrObj->nodeValue;

									if ($this->skipCombine($src)) {
										$hasSrc = false;
										break;
									}
								}

								// Do not add it to the combination list if it has "async" or "defer" attributes
								//removeIf(development)
									// @TODO: Consider combining all "asyncs" and all "defers"
								//endRemoveIf(development)
								if (in_array($attrObj->nodeName, array('async', 'defer'))) {
									$hasSrc = false;
									break;
								}
							}

							//removeIf(development)
								// It also checks the domain name to make sure no external scripts would be added to the list
								//$localAssetPath = CombineCommon::getLocalAssetPath( $src, 'js' );
							//endRemoveIf(development)
						}

						//removeIf(development)
							//$hasSrc = preg_match('/\<script(\s+.*?)(|\s+.*?)src=(|\s+|\'|")(.*?)\>/', $matchedSourceFromTag);
						//endRemoveIf(development)

						if ( $hasSrc ) {
							$localAssetPath = OptimizeCommon::getLocalAssetPath( $src, 'js' );

							if ( $localAssetPath ) {
								$combinableList[ $groupIndex ][] = array(
									'src'   => $src,
									'local' => $localAssetPath,
									'html'  => $matchedSourceFromTag
								);

								if ( strpos( $localAssetPath, '/wp-includes/js/jquery/jquery.js' ) !== false ) {
									$loadsLocaljQuery = true;
									$jQueryGroupIndex = $groupIndex;

									$jQueryArrayGroupKeys = array_keys( $combinableList[ $groupIndex ] );
									$jQueryScriptIndex    = array_pop( $jQueryArrayGroupKeys );

									$jQueryAndMigrateGroup ++;
								} elseif ( strpos( $localAssetPath,
										'/wp-includes/js/jquery/jquery-migrate.' ) !== false ) {
									$loadsLocaljQueryMigrate = true;
									$jQueryAndMigrateGroup ++;
								}
							}

							// We'll check the current group
							// If we have jQuery and jQuery migrate, we will consider the group completed
							// and we will move on to the next group
							if ( $jQueryAndMigrateGroup > 1 ) {
								$groupIndex ++;
								$jQueryAndMigrateGroup = 0; // reset it to avoid having one file per group!
							}

							// Have we passed <head> and stumbled upon the first script tag from the <body>
							// Then consider the group completed
							if ($lastScriptSrcFromHead && ($src === $lastScriptSrcFromHead)) {
								$groupIndex++;
								$reachedBody = true;
							}
						} else {
							$groupIndex ++;
						}

						if ($reachedBody && Main::instance()->settings['combine_loaded_js_defer_body']) {
							$bodyGroupIndexes[] = $groupIndex;
						}
					}

					// Is the page loading local jQuery but not local jQuery Migrate?
					// Keep jQuery as standalone file (not in the combinable list)
					if ( $loadsLocaljQuery && ! $loadsLocaljQueryMigrate && isset($jQueryScriptIndex) ) {
						unset($combinableList[$jQueryGroupIndex][$jQueryScriptIndex]);
					}

					// Could be pages such as maintenance mode with no external JavaScript files
					if (empty($combinableList)) {
						return $htmlSource;
					}

					$groupNo = 1;

					$finalCacheList = array();

					//removeIf(development)
						//return print_r($combinableList, true);
					//endRemoveIf(development)

					foreach ($combinableList as $groupIndex => $groupFiles) {
						// Any groups having one file? Then it's not really a group and the file should load on its own
						// Could be one extra file besides the jQuery & jQuery Migrate group or the only JS file called within the HEAD
						if (count($groupFiles) < 2) {
							continue;
						}

						$combinedUriPaths = $localAssetsPaths = $groupScriptTags = $groupScriptSrcs = array();

						foreach ( $groupFiles as $groupFileData ) {
							$src                      = $groupFileData['src'];
							$groupScriptSrcs[]        = $src;
							$combinedUriPaths[]       = OptimizeCommon::getHrefRelPath( $src );
							$localAssetsPaths[ $src ] = $groupFileData['local'];
							$groupScriptTags[]        = $groupFileData['html'];
						}

						$maybeDoJsCombine = $this->maybeDoJsCombine(
							sha1( implode( '', $combinedUriPaths ) ) . '-' . $groupNo,
							$localAssetsPaths
						);

						// Local path to combined CSS file
						$localFinalJsFile = $maybeDoJsCombine['local_final_js_file'];

						// URI (e.g. /wp-content/cache/asset-cleanup/[file-name-here.js]) to the combined JS file
						$uriToFinalJsFile = $maybeDoJsCombine['uri_final_js_file'];

						if ( ! file_exists( $localFinalJsFile ) ) {
							//removeIf(development)
								// TODO: to find a better solution
							//endRemoveIf(development)
							return $htmlSource; // something is not right as the file wasn't created, we will return the original HTML source
						}

						$groupScriptSrcsFilter = array_map( function ( $src ) {
							return str_replace( site_url(), '{site_url}', $src );
						}, $groupScriptSrcs );

						$groupScriptTagsFilter = array_map( function ( $scriptTag ) {
							return str_replace( site_url(), '{site_url}', $scriptTag );
						}, $groupScriptTags );

						$finalCacheList[ $groupNo ] = array(
							'uri_to_final_js_file' => $uriToFinalJsFile,
							'script_srcs'          => $groupScriptSrcsFilter,
							'script_tags'          => $groupScriptTagsFilter
						);

						if (in_array($groupIndex, $bodyGroupIndexes)) {
							$finalCacheList[ $groupNo ]['extras'][] = 'defer';
						}

						$groupNo++;
					}

					OptimizeCommon::setAssetCachedData($this->jsonStorageFile, self::$relPathJsCacheDir, json_encode($finalCacheList));
				}

				//removeIf(development)
					//return print_r($finalCacheList, true);
				//endRemoveIf(development)

				if (! empty($finalCacheList)) {
					foreach ( $finalCacheList as $groupNo => $cachedValues ) {
						$htmlSourceBeforeGroupReplacement = $htmlSource;

						$uriToFinalJsFile = $cachedValues['uri_to_final_js_file'];

						//removeIf(development)
							// All the cached files are created now (if they weren't there already)
							//if ($cachedFileExists) {
						//endRemoveIf(development)

						// Basic Combining (1) -> replace "first" tag with the final combination tag (there would be most likely multiple groups)
						// Enhanced Combining (2) -> replace "last" tag with the final combination tag (most likely one group)
						$indexReplacement = ($combineLevel === 2) ? (count($cachedValues['script_tags']) - 1) : 0;

						$finalTagUrl = WP_CONTENT_URL . self::$relPathJsCacheDir . $uriToFinalJsFile;

						$deferAttr = (isset($cachedValues['extras']) && in_array('defer', $cachedValues['extras'])) ? 'defer="defer"' : '';

						$finalJsTag = <<<HTML
<script {$deferAttr} id='asset-cleanup-combined-js-group-{$groupNo}' type='text/javascript' src='{$finalTagUrl}'></script>
HTML;
						$tagsStripped = 0;

						foreach ( $cachedValues['script_tags'] as $groupScriptTagIndex => $scriptTag ) {
							$scriptTag = str_replace( '{site_url}', site_url(), $scriptTag );

							if ( $groupScriptTagIndex === $indexReplacement ) {
								$htmlSourceBeforeTagReplacement = $htmlSource;
								$htmlSource = $this->strReplaceOnce( $scriptTag, $finalJsTag, $htmlSource );
							} else {
								$htmlSourceBeforeTagReplacement = $htmlSource;
								$htmlSource = $this->strReplaceOnce( $scriptTag, '', $htmlSource );
							}

							if ($htmlSource !== $htmlSourceBeforeTagReplacement) {
								$tagsStripped++;
							}
						}

						// At least two tags has have be stripped from the group to consider doing the group replacement
						// If the tags weren't replaced it's likely there were changes to their structure after they were cached for the group merging
						if ($tagsStripped < 2) {
							$htmlSource = $htmlSourceBeforeGroupReplacement;
						}
						//removeIf(development)
						//}
						//endRemoveIf(development)
					}
				}

				//removeIf(development)
					/*
					if ($jsCachedFileExists) {
						$finalTagUrl = WP_CONTENT_URL . self::$relPathJsCacheDir . $uriToFinalJsFile;

						$finalJsTag = <<<HTML
	<script id="asset-cleanup-combined-js" type='text/javascript' src="{$finalTagUrl}"></script>
	HTML;

						// Strip the chose files to avoid duplicate code as the final combined JS file will be added
						$htmlSource = CombineCommon::stripJustCombinedFileTags( $scriptSrcs, $htmlSource, 'js' );

						// Append the combined JS script tag
						if ( strpos( $htmlSource, CombineCommon::$combinedJsFileBeforeReplace ) !== false ) {
							$htmlSource = str_replace( CombineCommon::$combinedJsFileBeforeReplace, "\n" . $finalJsTag . "\n",
								$htmlSource );
						} else {
							// Just in case the comment where the replacement should have taken place is missing
							// Any other </body> strings will be ignored as only the first one from the HTML source code matters
							// e.g. some may use </body> inside a JavaScript code or part of a comment
							$htmlSource = preg_replace( '#</body>(|\s+)</html>#si',
								"\n" . $finalJsTag . "\n" . '</body>' . "\n" . '</html>', $htmlSource, 1 );
						}
					}
					*/
				//endRemoveIf(development)

				return $htmlSource;
			});
		}, 1);
	}

	/**
	 * @param $matchesSourcesFromTags
	 *
	 * @return mixed
	 */
	public function clearInlineScriptTags($matchesSourcesFromTags)
	{
		foreach ($matchesSourcesFromTags as $scriptTagIndex => $matchSourceFromTag) {
			$matchedSourceFromTag = trim( $matchSourceFromTag[0] );

			$domTag = new \DOMDocument();
			$domTag->loadHTML( $matchedSourceFromTag );

			foreach ( $domTag->getElementsByTagName( 'script' ) as $tagObject ) {
				$hasSrc = false;

				if ( ! $tagObject->hasAttributes() ) {
					$hasSrc = false;
				} else {
					// Has attributes? Check them
					foreach ( $tagObject->attributes as $attrObj ) {
						if ( $attrObj->nodeName === 'src' && $attrObj->nodeValue ) {
							$hasSrc = true;
						}
					}
				}

				if (! $hasSrc) {
					unset($matchesSourcesFromTags[$scriptTagIndex]);
				}
			}
		}

		return $matchesSourcesFromTags;
	}

	/**
	 * @param $htmlSource
	 *
	 * @return string
	 */
	public function lastScriptSrcFromHead($htmlSource)
	{
		$bodyHtml = Misc::extractBetween( $htmlSource, '<head', '</head>' );

		$regExpPattern = '#<script[^>]*>.*?</script>#is';

		//removeIf(development)
		//$regExpPattern = '/<script\\b[^>]*>(.*?)<\\/script>/i';
		//$regExpPattern = '/\<script(.*?)?\>(.*?)<\/script\>/i';
		//$regExpPattern = '/\<script(\s+.*?)(|\s+.*?)src=(|\s+|\'|")(.*?)\>(.*?)<\/script\>/i';
		//endRemoveIf(development)

		preg_match_all( $regExpPattern, $bodyHtml, $matchesSourcesFromTags, PREG_SET_ORDER );

		// Only keep combinable JS files
		foreach ( array_reverse($matchesSourcesFromTags) as $matchSourceFromTag ) {
			//removeIf(development)
			//return print_r($matchSourceFromTag, true);
			//endRemoveIf(development)

			$matchedSourceFromTag = trim( $matchSourceFromTag[0] );

			$domTag = new \DOMDocument();
			$domTag->loadHTML( $matchedSourceFromTag );

			foreach ( $domTag->getElementsByTagName( 'script' ) as $tagObject ) {
				if ( ! $tagObject->hasAttributes() ) {
					continue;
				}

				foreach ( $tagObject->attributes as $attrObj ) {
					if ( $attrObj->nodeName === 'src' && $attrObj->nodeValue ) {
						return (string) $attrObj->nodeValue;
						break;
					}
				}
			}
		}

		return '';
	}

	/**
	 * @param $shaOneCombinedUriPaths
	 * @param $localAssetsPaths
	 *
	 * @return array
	 */
	public function maybeDoJsCombine($shaOneCombinedUriPaths, $localAssetsPaths)
	{
		$current_user = wp_get_current_user();
		$dirToUserCachedFile = ((isset($current_user->ID) && $current_user->ID > 0) ? 'logged-in/'.$current_user->ID.'/' : '');

		$uriToFinalJsFile = $dirToUserCachedFile . $shaOneCombinedUriPaths . '.js';

		$localFinalJsFile = WP_CONTENT_DIR . self::$relPathJsCacheDir . $uriToFinalJsFile;
		$localDirForJsFile = WP_CONTENT_DIR . self::$relPathJsCacheDir . $dirToUserCachedFile;

		// Only combine if $shaOneCombinedUriPaths.js does not exist
		// If "?ver" value changes on any of the assets or the asset list changes in any way
		// then $shaOneCombinedUriPaths will change too and a new JS file will be generated and loaded

		//removeIf(development)
			// || defined('WPACU_DEV_ALWAYS_GENERATE_COMBINED_JS'
		//endRemoveIf(development)

		$skipIfFileExists = true;

		if ($skipIfFileExists || ! file_exists($localFinalJsFile)) {
			// Change $assetsContents as paths to fonts and images that are relative (e.g. ../, ../../) have to be updated
			$finalJsContentsGroupsArray = array();

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

				$jsContent = @file_get_contents($localAssetsPath);

				if ($jsContent) {
					//removeIf(development)
						/*
						if ($this->hasjQueryDocumentReady($jsContent)) {
							$finalJsContentsIndex = 2;
						} else {
							// The ones without document.ready() will take priority
							$finalJsContentsIndex = 1;
						}
						*/
					//endRemoveIf(development)

					$finalJsContentsIndex = 1;

					$finalJsContentsGroupsArray[$finalJsContentsIndex][] = self::maybeDoJsFixes($jsContent, $pathToAssetDir . '/') . "\n\n";
				}
			}

			if (! empty($finalJsContentsGroupsArray)) {
				$finalJsContents = implode( '', $finalJsContentsGroupsArray[1] ) . implode( '',
						$finalJsContentsGroupsArray[2] );

				if ( $dirToUserCachedFile !== '' && isset( $current_user->ID ) && $current_user->ID > 0
				     && ! mkdir( $localDirForJsFile ) && ! is_dir( $localDirForJsFile) ) {
						return array( 'uri_final_js_file' => '', 'local_final_js_file' => '' );
				}

				@file_put_contents( $localFinalJsFile, $finalJsContents );
			}
		}

		return array(
			'uri_final_js_file'   => $uriToFinalJsFile,
			'local_final_js_file' => $localFinalJsFile
		);
	}

	//removeIf(development)
		/**
		 * @param $jsContent
		 *
		 * @return bool
		 */
		/*
		public function hasjQueryDocumentReady($jsContent)
		{
			// Matches:
			// 1) jQuery( document ).ready(function()
			// 2) $(document).ready( function () {
			// 3) jQuery (document) . ready etc.
			if (preg_match('/\((|\s+)document(|\s+)\)(|\s+)(\.|\s)(|\s+)ready/i', $jsContent)) {
				return true;
			}

			// In case the RegExp fails:
			if (strpos($jsContent, '$(document).ready(function()') !== false) {
				return true;
			}

			if (strpos($jsContent, 'jQuery(document).ready(function(') !== false) {
				return true;
			}

			return false;
		}
		*/
	//endRemoveIf(development)

	/**
	 * @param $jsContent
	 * @param $appendBefore
	 *
	 * @return mixed
	 */
	public static function maybeDoJsFixes($jsContent, $appendBefore)
	{
		// Relative URIs for CSS Paths
		// For code such as:
		// $(this).css("background", "url('../images/image-1.jpg')");
		$jsContent = str_replace(
			array('url("../', "url('../", 'url(../'),
			array('url("'.$appendBefore.'../', "url('".$appendBefore.'../', 'url('.$appendBefore.'../'),
			$jsContent
		);

		$jsContent = trim($jsContent);

		//removeIf(development)
			//if (in_array(substr($jsContent, -1), array(')', '}'))) {
		//endRemoveIf(development)
		if (substr($jsContent, -1) !== ';') {
			$jsContent .= "\n" . ';'; // add semicolon as the last character
		}
		//removeIf(development)
			//}
		//endRemoveIf(development)

		return $jsContent;
	}

	/**
	 * @param $src
	 *
	 * @return bool
	 */
	public function skipCombine($src)
	{
		$regExps = array();

		if (Main::instance()->settings['combine_loaded_js_exceptions'] !== '') {
			$loadedCssExceptionsPatterns = trim(Main::instance()->settings['combine_loaded_js_exceptions']);

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
			if ( preg_match( $regExp, $src ) ) {
				// Skip combination
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function doJsCombine()
	{
		// No JS files are combined in the Dashboard
		// Always in the front-end view
		// Do not combine if there's a POST request as there could be assets loading conditionally
		// that might not be needed when the page is accessed without POST, making the final JS file larger
		if (! empty($_POST) || is_admin()) {
			return false; // Do not combine
		}

		// Only clean request URIs allowed (with few exceptions)
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
			return false; // Do not combine anything if "Test Mode" is ON
		}

		if ($pluginSettings['combine_loaded_js'] === '') {
			return false; // Do not combine
		}

		if ( ($pluginSettings['combine_loaded_js'] === 'for_admin'
		     || $pluginSettings['combine_loaded_js_for_admin_only'] == 1)
		    && Menu::userCanManageAssets() ) {
			return true; // Do combine
		}

		if ( $pluginSettings['combine_loaded_js_for_admin_only'] === ''
		    && in_array($pluginSettings['combine_loaded_js'], array('for_all', 1)) ) {
			return true; // Do combine
		}

		// Finally, return false as none of the checks above matched
		return false;
	}

	/**
	 * @param $strFind
	 * @param $strReplaceWith
	 * @param $string
	 *
	 * @return mixed
	 */
	public static function strReplaceOnce($strFind, $strReplaceWith, $string)
	{
		if ( strpos($string, $strFind) === false ) {
			return $string;
		}

		$occurrence = strpos($string, $strFind);
		return substr_replace($string, $strReplaceWith, $occurrence, strlen($strFind));
	}

	//removeIf(development)
		/**
		 * Triggers in the front-end view only for the current viewed page
		 */
		/*
		public function clearJsCacheFile()
		{
			$optionValue = get_transient($this->transientJsName);

			if ($optionValue) {
				$this->removeCachedJsFile($optionValue);
			}
		}
		*/

		/**
		 * @param $optionValue
		 */
		/*
		public function removeCachedJsFile($optionValue)
		{
			$optionValueArray  = json_decode($optionValue, ARRAY_A);
			$uriToFinalJsFile = $optionValueArray['uri_to_final_js_file'];

			$fullLocalPathToCachedJsFile = WP_CONTENT_DIR . self::$relPathJsCacheDir . $uriToFinalJsFile;

			// Check the extension to make sure a JS file is in fact deleted from the cache
			if (file_exists($fullLocalPathToCachedJsFile) && strrchr($uriToFinalJsFile, '.') === '.js') {
				@unlink($fullLocalPathToCachedJsFile);
			}
		}
		*/
	//endRemoveIf(development)
}
