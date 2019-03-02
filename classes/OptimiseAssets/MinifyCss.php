<?php
namespace WpAssetCleanUp\OptimiseAssets;

use WpAssetCleanUp\Main;
use WpAssetCleanUp\Menu;
use WpAssetCleanUp\Misc;
use WpAssetCleanUp\MetaBoxes;

/**
 * Class MinifyCss
 * @package WpAssetCleanUp\OptimiseAssets
 */
class MinifyCss
{
	/**
	 * MinifyCss constructor.
	 */
	public function __construct()
	{
		add_action('wp_footer', function() {
			if ( array_key_exists('wpacu_no_css_minify', $_GET) || // not on query string request (debugging purposes)
			     is_admin() || // not for Dashboard view
			    (! Main::instance()->settings['minify_loaded_css']) || // Minify CSS has to be Enabled
			     (Main::instance()->settings['test_mode'] && ! Menu::userCanManageAssets()) ) { // Does not trigger if "Test Mode" is Enabled
				return;
			}

			if (defined('WPACU_CURRENT_PAGE_ID') && WPACU_CURRENT_PAGE_ID > 0 && is_singular()) {
				// If "Do not minify CSS on this page" is checked in "Asset CleanUp: Options" side meta box
				$pageOptions = MetaBoxes::getPageOptions( WPACU_CURRENT_PAGE_ID );

				if ( isset( $pageOptions['no_css_minify'] ) && $pageOptions['no_css_minify'] ) {
					return;
				}
			}

			global $wp_styles;

			$cssMinifyList = array();

			// [Start] Collect for caching
			foreach ($wp_styles->done as $handle) {
				if (isset($wp_styles->registered[$handle])) {
					$value = $wp_styles->registered[$handle];
					$minifyValues = $this->maybeMinifyIt( $value );

					if ( ! empty( $minifyValues ) ) {
						$cssMinifyList[] = $minifyValues;
					}
				}
			}

			wp_cache_add('wpacu_css_minify_list', $cssMinifyList);
			// [End] Collect for caching
		}, 1);

		// Alter the HTML source by updating the original link URLs with the cached ones
	}

	/**
	 * @param $htmlSource
	 *
	 * @return mixed
	 */
	public static function updateHtmlSourceOriginalToMinCss($htmlSource)
	{
		$cssMinifyList = wp_cache_get('wpacu_css_minify_list');

		// This will be taken from the transient
		//removeIf(development)
			//$cssMinifyList = get_transient();
		//endRemoveIf(development)
		if (empty($cssMinifyList)) {
			return $htmlSource;
		}

		$regExpPattern = '#<link[^>]*(stylesheet|preload)[^>]*(>)#Usmi';

		preg_match_all($regExpPattern, OptimizeCommon::cleanerHtmlSource($htmlSource), $matchesSourcesFromTags, PREG_SET_ORDER);

		//removeIf(development)
			//return print_r($matchesSourcesFromTags, true);
			//return print_r($cssMinifyList, true);
		//endRemoveIf(development)

		foreach ($matchesSourcesFromTags as $matches) {
			$linkSourceTag = $matches[0];

			if (strip_tags($linkSourceTag) !== '') {
				// Hmm? Not a valid tag... Skip it...
				continue;
			}

			foreach ($cssMinifyList as $listValues) {
				$sourceUrl = site_url() . $listValues[0];
				$minUrl    = site_url() . $listValues[1];

				$newLinkSourceTag = str_ireplace($sourceUrl, $minUrl, $linkSourceTag);

				if ($linkSourceTag !== $newLinkSourceTag) {
					// Strip ?ver=
					$toStrip = Misc::extractBetween($newLinkSourceTag, '?ver=', ' ');

					if (in_array(substr($toStrip, -1), array('"', "'"))) {
						$toStrip = '?ver='. trim(trim($toStrip, '"'), "'");
						$newLinkSourceTag = str_replace($toStrip, '', $newLinkSourceTag);
					}

					$htmlSource = str_replace($linkSourceTag, $newLinkSourceTag, $htmlSource);
					break;
				}
			}

			//removeIf(development)
				//$htmlSource .= $newLinkSourceTag."\n";
			//endRemoveIf(development)
		}

		//removeIf(development)
			//return print_r($matches, true);
		//endRemoveIf(development)
		return $htmlSource;
	}

	/**
	 * @param $value
	 *
	 * @return array
	 */
	public function maybeMinifyIt($value)
	{
		global $wp_version;

		$src = isset($value->src) ? $value->src : false;

		if (! $src || $this->skipMinify($src)) {
			return array();
		}

		$handleDbStr = md5($value->handle);

		$transientName = 'wpacu_css_minify_'.$handleDbStr;

		//removeIf(development)
		$skipCache = false;
		//endRemoveIf(development)

		//removeIf(development)
		if (! $skipCache) {
		//endRemoveIf(development)

			$savedValues = get_transient( $transientName );

			if ( $savedValues ) {
				$savedValuesArray = json_decode( $savedValues, ARRAY_A );

				if ( $savedValuesArray['ver'] !== $value->ver ) {
					// New File Version? Delete transient as it will be re-added to the database with the new version
					delete_transient( $transientName );
				} else {
					$localPathToCssMin = str_replace( '//', '/', ABSPATH . $savedValuesArray['min_uri'] );

					if ( isset( $savedValuesArray['source_uri'] ) && file_exists( $localPathToCssMin ) ) {
						return array(
							$savedValuesArray['source_uri'],
							$savedValuesArray['min_uri'],
							//removeIf(development)
								//$value->ver
							//endRemoveIf(development)
						);
					}
				}
			}

		//removeIf(development)
		}
		//endRemoveIf(development)

		if (strpos($src, '/wp-includes/') === 0) {
			$src = site_url() . $src;
		}

		$localAssetPath = OptimizeCommon::getLocalAssetPath($src, 'css');

		if (! file_exists($localAssetPath)) {
			return array();
		}

		$assetHref = $value->src;

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

		$cssContent = @file_get_contents($localAssetPath);
		$cssContent = OptimizeCss::maybeFixCssBackgroundUrls($cssContent, $pathToAssetDir . '/'); // Minify it and save it to /wp-content/cache/css/min/

		$cssContent = self::applyMinification($cssContent);

		// Relative path to the new file
		$ver = (isset($value->ver) && $value->ver) ? $value->ver : $wp_version;

		$newFilePathUri  = OptimizeCss::$relPathCssCacheDir . 'min/' . $value->handle . '-v' . $ver . '.css';

		$newLocalPath    = WP_CONTENT_DIR . $newFilePathUri; // Ful Local path
		$newLocalPathUrl = WP_CONTENT_URL . $newFilePathUri; // Full URL path

		$saveFile = @file_put_contents($newLocalPath, $cssContent);

		if (! $saveFile && ! $cssContent) {
			return array();
		}

		$saveValues = array(
			//removeIf(development)
				//'handle'     => $value->handle,
			//endRemoveIf(development)
			'source_uri' => OptimizeCommon::getHrefRelPath($value->src),
			'min_uri'    => OptimizeCommon::getHrefRelPath($newLocalPathUrl),
			'ver'        => $ver
		);

		// Add / Re-add (with new version) transient
		set_transient($transientName, json_encode($saveValues));

		return array(
			OptimizeCommon::getHrefRelPath($value->src),
			OptimizeCommon::getHrefRelPath($newLocalPathUrl),
			//removeIf(development)
				//$value->ver
			//endRemoveIf(development)
		);
	}

	/**
	 * @param $cssContent
	 *
	 * @return string|string[]|null
	 */
	public static function applyMinification($cssContent)
	{
		// Replace multiple whitespace with only one
		$cssContent = preg_replace( '/\s+/', ' ', $cssContent );

		// Remove comment blocks, everything between /* and */, except the ones preserved with /*! ... */ or /** ... */
		$cssContent = preg_replace( '~/\*(?![\!|\*])(.*?)\*/~', '', $cssContent );

		// Remove ; before }
		$cssContent = preg_replace( '/;(?=\s*})/', '', $cssContent );

		// Remove space after , : ; { } */ >
		$cssContent = preg_replace( '/(,|:|;|\{|}|\*\/|>) /', '$1', $cssContent );

		// Remove space before , ; { } >
		$cssContent = preg_replace( '/ (,|;|\{|}|>)/', '$1', $cssContent );

		// Strip units such as px,em,pt etc. if value is 0 (converts 0px to 0)
		$cssContent = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $cssContent );

		// Strip leading 0 on decimal values (converts 0.5px into .5px)
		$cssContent = preg_replace( '/(:| )0\.(\d+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $cssContent );

		// Converts #ff000 to #f00
		$cssContent = preg_replace("/#([0-9a-fA-F])\\1([0-9a-fA-F])\\2([0-9a-fA-F])\\3/", '#$1$2$3', $cssContent);

		$strReps = array(
			// Converts things such as "margin:0 0 0 0;" to "margin:0;"
			':0 0 0 0;' => ':0;'
			/*
			//removeIf(development)
				// Further space reduction
				'} '          => '}',
				' !important' => '!important'
			//endRemoveIf(development)
			*/
		);

		$cssContent = str_replace(array_keys($strReps), array_values($strReps), $cssContent);

		// Remove whitespaces before and after the content
		return trim($cssContent);
	}

	/**
	 * @param $src
	 *
	 * @return bool
	 */
	public function skipMinify($src)
	{

		$regExps = array(
			'#/wp-content/plugins/wp-asset-clean-up(.*?).min.css#',

			// Other libraries from the core that end in .min.js
			'#/wp-includes/css/(.*?).min.css#',

			//removeIf(development)
				/*
				// Font Awesome
				'#/font-awesome(.*?).min.css#',

				// Bootstrap
				'#/bootstrap(.*?).min.css#',

				// Elementor .min.css
				'#/wp-content/plugins/elementor/assets/(.*?).min.css#',

				// WooCommerce Assets
				'#/wp-content/plugins/woocommerce/assets/css/(.*?).min.css#',

				// Easy Digital Downloads Assets
				'#/wp-content/plugins/easy-digital-downloads/assets/css/(.*?).min.css#'
				*/
			//endRemoveIf(development)
		);

		if (Main::instance()->settings['minify_loaded_css_exceptions'] !== '') {
			$loadedCssExceptionsPatterns = trim(Main::instance()->settings['minify_loaded_css_exceptions']);

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

		foreach ($regExps as $regExp) {
			if ( preg_match( $regExp, $src ) ) {
				return true;
			}
		}

		return false;
	}
}
