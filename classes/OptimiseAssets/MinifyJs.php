<?php
namespace WpAssetCleanUp\OptimiseAssets;

use WpAssetCleanUp\Main;
use WpAssetCleanUp\Menu;
use WpAssetCleanUp\Misc;
use WpAssetCleanUp\MetaBoxes;

/**
 * Class MinifyJs
 * @package WpAssetCleanUp\OptimiseAssets
 */
class MinifyJs
{
	/**
	 * MinifyJs constructor.
	 */
	public function __construct()
	{
		/*
		 * #minifying
		 * STEP 1: Prepare minify-able caching list
		 */
		add_action('wp_print_footer_scripts', function() {
			if ( array_key_exists('wpacu_no_js_minify', $_GET) || // not on query string request (debugging purposes)
			     is_admin() || // not for Dashboard view
			     (! Main::instance()->settings['minify_loaded_js']) || // Minify JS has to be Enabled
			     (Main::instance()->settings['test_mode'] && ! Menu::userCanManageAssets()) ) { // Does not trigger if "Test Mode" is Enabled
				return;
			}

			if (defined('WPACU_CURRENT_PAGE_ID') && WPACU_CURRENT_PAGE_ID > 0 && is_singular()) {
				// If "Do not minify JS on this page" is checked in "Asset CleanUp: Options" side meta box
				$pageOptions = MetaBoxes::getPageOptions( WPACU_CURRENT_PAGE_ID );

				if ( isset( $pageOptions['no_js_minify'] ) && $pageOptions['no_js_minify'] ) {
					return;
				}
			}

			global $wp_scripts;

			$jsMinifyList = array();

			$wpScriptsList = array_unique(array_merge($wp_scripts->done, $wp_scripts->queue));

			// [Start] Collect for caching
			foreach ($wpScriptsList as $handle) {
				if (isset($wp_scripts->registered[$handle])) {
					$value = $wp_scripts->registered[$handle];
					$minifyValues = $this->maybeMinifyIt( $value );

					if ( ! empty( $minifyValues ) ) {
						$jsMinifyList[] = $minifyValues;
					}
				}
			}

			//removeIf(development)
				//echo '<pre>'; print_r($jsMinifyList); exit;
			//endRemoveIf(development)

			wp_cache_add('wpacu_js_minify_list', $jsMinifyList);
			// [End] Collect for caching
		}, PHP_INT_MAX);
	}

	/**
	 * @param $htmlSource
	 *
	 * @return mixed
	 */
	public static function updateHtmlSourceOriginalToMinJs($htmlSource)
	{
		$jsMinifyList = wp_cache_get('wpacu_js_minify_list');

		//removeIf(development)
			//return print_r($jsMinifyList, true);
		//endRemoveIf(development)

		if (empty($jsMinifyList)) {
			return $htmlSource;
		}

		$regExpPattern = '#<script[^>]*src(|\s+)=(|\s+)[^>]*(>)#Usmi';

		preg_match_all($regExpPattern, OptimizeCommon::cleanerHtmlSource($htmlSource), $matchesSourcesFromTags, PREG_SET_ORDER);

		//removeIf(development)
			//return print_r($matchesSourcesFromTags, true);
		//endRemoveIf(development)

		foreach ($matchesSourcesFromTags as $matches) {
			$scriptSourceTag = $matches[0];

			if (strip_tags($scriptSourceTag) !== '') {
				// Hmm? Not a valid tag... Skip it...
				continue;
			}

			foreach ($jsMinifyList as $listValues) {
				$sourceUrl = site_url() . $listValues[0];
				$minUrl    = site_url() . $listValues[1];

				$newScriptSourceTag = str_ireplace($sourceUrl, $minUrl, $scriptSourceTag);

				if ($scriptSourceTag !== $newScriptSourceTag) {
					// Strip ?ver=
					$toStrip = Misc::extractBetween($newScriptSourceTag, '?ver=', '>');

					if (in_array(substr($toStrip, -1), array('"', "'"))) {
						$toStrip = '?ver='. trim(trim($toStrip, '"'), "'");
						$newScriptSourceTag = str_replace($toStrip, '', $newScriptSourceTag);
					}

					$htmlSource = str_replace($scriptSourceTag, $newScriptSourceTag, $htmlSource);
					break;
				}
			}
			//removeIf(development)
				//$htmlSource .= $newScriptSourceTag."\n";
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

		$transientName = 'wpacu_js_minify_'.$handleDbStr;

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
					$localPathToJsMin = str_replace( '//', '/', ABSPATH . $savedValuesArray['min_uri'] );

					// Do not load any minified JS file (from the database transient cache) if it doesn't exist
					// It will fallback to the original JS file
					if ( isset( $savedValuesArray['source_uri'] ) && file_exists( $localPathToJsMin ) ) {
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

		$localAssetPath = OptimizeCommon::getLocalAssetPath($src, 'js');

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

		$jsContent = @file_get_contents($localAssetPath);
		$jsContent = OptimizeJs::maybeDoJsFixes($jsContent, $pathToAssetDir . '/'); // Minify it and save it to /wp-content/cache/js/min/

		$jsContent = self::applyMinification($jsContent);

		// Relative path to the new file
		$ver = (isset($value->ver) && $value->ver) ? $value->ver : $wp_version;

		$newFilePathUri  = OptimizeJs::$relPathJsCacheDir . 'min/' . $value->handle . '-v' . $ver . '.js';

		$newLocalPath    = WP_CONTENT_DIR . $newFilePathUri; // Ful Local path
		$newLocalPathUrl = WP_CONTENT_URL . $newFilePathUri; // Full URL path

		$saveFile = @file_put_contents($newLocalPath, $jsContent);

		if (! $saveFile || ! $jsContent) {
			// Fallback to the original JS if the minified version can't be created or updated
			return array();
		}

		$saveValues = array(
			//removeIf(development)
				//'handle'   => $value->handle,
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
	 * @param $jsContent
	 *
	 * @return string|string[]|null
	 */
	public static function applyMinification($jsContent)
	{
		$jsContent = preg_replace(array("/\s+\n/", "/\n\s+/", '/ +/'), array("\n", "\n ", ' '), $jsContent);

		// Going line by line
		$jsContentsLines = explode( "\n", $jsContent );

		$jsContent = '';

		foreach ( $jsContentsLines as $jsLineIndex => $jsContentLine ) {
			$jsContentLine = trim( $jsContentLine );

			//removeIf(development)
				/*
				if (strpos($jsContentLine, '//') === 0
					|| strpos($jsContentLine, '/*') === 0
					|| strpos($jsContentLine, '*') === 0) {
					continue;
				}
				*/
			//endRemoveIf(development)

			if (strpos(trim($jsContentLine), '//') === 0) {
				continue;
			}

			$appendNewLine = true;
			$mergeDelimiter = '';

			if (strpos($jsContentLine, '//') !== false) {
				$appendNewLine = true;
			}

			// When to keep the new line
			elseif ( strpos( $jsContentLine, '/*' ) !== false
			         || strpos( $jsContentLine, '*/' ) !== false
			         || strpos( $jsContentLine, '*' ) === 0
			         || in_array(substr( trim( $jsContentLine ),
					- 1 ), array('}', ')')) // Later, consider a solution to skip this from having a new line added
			) {
				$appendNewLine = true;
			} else {
				$mergeDelimiter = in_array(
					substr( trim( $jsContentLine ), - 1 ),
					array( '{', '}', ';', ',' )
				) ? '' : ' ';
			}

			$jsContent .= self::basicReplacementOnLine($jsContentLine) . ($appendNewLine ? "\n" : $mergeDelimiter);
		}

		//removeIf(development)
			//return $jsContent;
		//endRemoveIf(development)

		//removeIf(development)
			// Remove new lines after ;
			//$jsContent = preg_replace("/;?\n/", ';', $jsContent);
		//endRemoveIf(development)

		/*
		 * Step 1: Make sure content between quotes (could be message alerts, plain text) is not replaced
		 *         It will be replaced later on
		 */
		preg_match_all("/(\"(.*?)\")|('(.*?)')/", $jsContent,$matchesBetweenQuotes);

		//removeIf(development)
			//return print_r($matchesBetweenQuotes, true);
		//endRemoveIf(development)

		$wpacuSpaceDel = '@[wpacu-plugin-space-del]@';

		if (isset($matchesBetweenQuotes[0]) && ! empty($matchesBetweenQuotes[0])) {
			foreach ($matchesBetweenQuotes[0] as $matchBetweenQuotes) {
				if (strpos($matchBetweenQuotes, ' ') !== false) {
					$newMatch  = str_replace( ' ', $wpacuSpaceDel, $matchBetweenQuotes );
					$jsContent = str_replace( $matchBetweenQuotes, $newMatch, $jsContent );
				}
			}
		}

		// Source: https://github.com/Letractively/samstyle-php-framework/blob/master/sp.php
		$regex = array(
			"`^([\t\s]+)`sm" => '',
			"`^\/\*(.*?)\*\/`sm" => '',
			"`([\n\A;]+)\/\*(.+?)\*\/`ism" => '$1',
			"`([\n\A;\s]+)//(.+?)[\n\r]`ism" => "$1\n",
			"`(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+`ism" => "\n",

			"/}\);\n}\)/" => '});})',

			//removeIf(development)
				//"/}\n}\n/" => "}}\n",
			//endRemoveIf(development)

			//removeIf(development)
				/*
				 (function($) {
				  var
					TO
				 (function($) {var
				 */
				//"/({|}|\)|;|,)(\n+)(var|if|this|return|jQuery|\.|_|}|{|'|\"|\\$)/i" => '$1 $3',

				//"/([{|}|\)|;|,]+)(\n+)(var|if|else|for|this|return|ready|jQuery|\\$|})/i" => '$1 $3',
			//endRemoveIf(development)

			"/([{|;|,]+)(\n+)('|\"|var|if|else|for|this|return|ready|jQuery|\\$|})/i" => '$1 $3',

			'/} else {/i' => '}else{',
			'/if \(/i' => 'if('

			//removeIf(development)
				//'/(|\s+):(|\s+)/' => ':'
			//endRemoveIf(development)
		);

		$jsContent = preg_replace(array_keys($regex), array_values($regex), $jsContent);

		$newReps = array(
			";\n" => ';',
			//",\n" => ',',
			"}\n}" => '}}'
		);

		$jsContent = str_replace(array_keys($newReps), array_values($newReps), $jsContent);

		/*
		 * Step: Make sure content between quotes (could be message alerts, plain text) is not replaced
		 * Restore the spacing between quotes
		 */
		$jsContent = str_replace($wpacuSpaceDel, ' ', $jsContent);

		// Remove whitespaces before and after the content
		return trim($jsContent);
	}

	/**
	 * @param $jsContentLine
	 *
	 * @return mixed
	 */
	public static function basicReplacementOnLine($jsContentLine)
	{
		// Regular Expression in the line? Don't make any changes
		if (   strpos($jsContentLine, 'RegExp') !== false
		    || preg_match('/\=\s\//', $jsContentLine)) {
			return $jsContentLine;
		}

		$repsOne = array(
			// Remove space before & after colons
			' :' => ':',
			': ' => ':',

			// Remove space before & after equal signs
			' =' => '=',
			'= ' => '=',

			"' ? '" => "'?'",
			') {'   => '){',
			') !'   => ')!'
		);
		$jsContentLine = str_replace(array_keys($repsOne), array_values($repsOne), $jsContentLine);

		$repsTwo = array(
			"{ '" => "{'",
			"' }" => "'}",
			", '" => ",'",
			' || ' => '||',

			'=true;' => '=!0;',
			':true;' => ':!0;',
			'(true)' => '(!0)',
			'(true,' => '(!0,',
			'return true;' => 'return !0;',
			'return true}' => 'return !0}',

			'=false;' => '=!1;',
			':false;' => ':!1;',
			'(false)' => '(!1)',
			'(false,' => '(!1,',
			'return false;' => 'return !1;',
			'return false}' => 'return !1}',

			//removeIf(development)
				//';}'   => '}'
			//endRemoveIf(development)
		);

		$jsContentLine = str_ireplace(array_keys($repsTwo), array_values($repsTwo), $jsContentLine);

		//removeIf(development)
			// TODO: Check how many resources are used
			// Go through the file line by line
		//endRemoveIf(development)

		$repsThree = array(
			'; '  => ';',
			'{ '  => '{',
			'} '  => '}',
			'( '  => '(',

			//removeIf(development)
				//' ('  => '(',
				//' )'  => ')',
				//') '  => ')',
			//endRemoveIf(development)

			', '  => ',',
			' + ' => '+'

			//removeIf(development)
				//';}'  => '}'
			//endRemoveIf(development)
		);

		$jsContentLine = str_ireplace(array_keys($repsThree), array_values($repsThree), $jsContentLine);

		return $jsContentLine;
	}

	/**
	 * @param $src
	 *
	 * @return bool
	 */
	public function skipMinify($src)
	{
		$regExps = array(
			'#/wp-content/plugins/wp-asset-clean-up(.*?).min.js#',

			// Other libraries from the core that end in .min.js
			'#/wp-includes/(.*?).min.js#',

			// jQuery library
			'#/wp-includes/js/jquery/jquery.js#'

			//removeIf(development)
				/*
				// WP-Rocket Lazyload
				'#/wp-content/plugins/wp-rocket/inc/front/js/lazyload(.*?).min.js#',

				// Elementor .min.js
				'#/wp-content/plugins/elementor/assets/(.*?).min.js#',

				// WooCommerce Assets
				'#/wp-content/plugins/woocommerce/assets/js/(.*?).min.js#',

				// Easy Digital Downloads Assets
				'#/wp-content/plugins/easy-digital-downloads/assets/js/(.*?).min.js#',

				// NextGen Gallery Assets
				'#/wp-content/plugins/nextgen-gallery/(.*).min.js#'
				*/
			//endRemoveIf(development)
		);

		if (Main::instance()->settings['minify_loaded_js_exceptions'] !== '') {
			$loadedJsExceptionsPatterns = trim(Main::instance()->settings['minify_loaded_js_exceptions']);

			if (strpos($loadedJsExceptionsPatterns, "\n")) {
				// Multiple values (one per line)
				foreach ($loadedJsExceptionsPatterns as $loadedJsExceptionPattern) {
					$regExps[] = '#'.$loadedJsExceptionPattern.'#';
				}
			} else {
				// Only one value?
				$regExps[] = '#'.$loadedJsExceptionsPatterns.'#';
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
