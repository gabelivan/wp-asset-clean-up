<?php
namespace WpAssetCleanUp;

/**
 * Class CleanUp
 * @package WpAssetCleanUpPro
 */
class CleanUp
{
	/**
	 *
	 */
	public function init()
	{
		// Is "Test Mode" is enabled and the page is viewed by a regular visitor (not administrator with plugin activation privileges)?
		// Stop here as the script will NOT PREVENT any of the elements below to load
		// They will load as they used to for the regular visitor while the admin debugs the website
		add_action('init', function() {
			if ( Main::instance()->preventUnloadAssets() ) {
				return;
			}

			CleanUp::doClean();
		});
	}

	/**
	 *
	 */
	public function doClean()
	{
		$settings = Main::instance()->settings;

		// Remove "Really Simple Discovery (RSD)" link?
		if ($settings['remove_rsd_link'] == 1) {
			// <link rel="EditURI" type="application/rsd+xml" title="RSD" href="https://yourwebsite.com/xmlrpc.php?rsd" />
			remove_action('wp_head', 'rsd_link');
		}

		// Remove "Windows Live Writer" link?
		if ($settings['remove_wlw_link'] == 1) {
			// <link rel="wlwmanifest" type="application/wlwmanifest+xml" href="http://yourwebsite.com/wp-includes/wlwmanifest.xml">
			remove_action('wp_head', 'wlwmanifest_link');
		}

		// Remove "REST API" link?
		if ($settings['remove_rest_api_link'] == 1) {
			// <link rel='https://api.w.org/' href='https://yourwebsite.com/wp-json/' />
			remove_action('wp_head', 'rest_output_link_wp_head');
		}

		// Remove "Shortlink"?
		if ($settings['remove_shortlink'] == 1) {
			// <link rel='shortlink' href="https://yourdomain.com/?p=1">
			remove_action('wp_head', 'wp_shortlink_wp_head');
		}

		// Remove "Post's Relational Links"?
		if ($settings['remove_posts_rel_links'] == 1) {
			// <link rel='prev' title='Title of adjacent post' href='https://yourdomain.com/adjacent-post-slug-here/' />
			remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
		}

		// Remove "WordPress version" tag?
		if ($settings['remove_wp_version']) {
			// <meta name="generator" content="WordPress 4.9.8" />
			remove_action('wp_head', 'wp_generator');

			// also hide it from RSS
			add_filter('the_generator', '__return_false');
		}

		// Remove Main RSS Feed Link?
		if ($settings['remove_main_feed_link']) {
			add_filter('feed_links_show_posts_feed', '__return_false');
			remove_action('wp_head', 'feed_links_extra', 3);
		}

		// Remove Comment RSS Feed Link?
		if ($settings['remove_comment_feed_link']) {
			add_filter('feed_links_show_comments_feed', '__return_false');
		}

		// Remove "WordPress version" and all other "generator" meta tags?
		if ($settings['remove_generator_tag']) {
			add_action('wp_loaded', function () {
				ob_start(function ($htmlSource) {
					return self::removeMetaGenerators($htmlSource);
				});
			} );
		}

		// Disable XML-RPC protocol support (partially or completely)
		if (in_array($settings['disable_xmlrpc'], array('disable_all', 'disable_pingback'))) {
			// Partially or Completely Options / Pingback will be disabled
			$this->disableXmlRpcPingback();

			// Complete disable the service
			if ($settings['disable_xmlrpc'] === 'disable_all') {
				add_filter('xmlrpc_enabled', '__return_false');
			}

			// Also clean it up from the <head>
			add_action('wp_loaded', function() {
				ob_start(function ($htmlSource) {
					$pingBackUrl = get_bloginfo('pingback_url');

					$matchRegExps = array(
						'#<link rel=("|\')pingback("|\') href=("|\')'.$pingBackUrl.'("|\')( /|)>#',
						'#<link href=("|\')'.$pingBackUrl.'("|\') rel=("|\')pingback("|\')( /|)>#'
					);

					foreach ($matchRegExps as $matchRegExp) {
						$htmlSource = preg_replace($matchRegExp, '', $htmlSource);
					}

					return $htmlSource;
				});
			});
		}
	}

	/**
	 *
	 */
	public function disableXmlRpcPingback()
	{
		// Disable Pingback method
		add_filter('xmlrpc_methods', function ($methods) {
			unset($methods['pingback.ping'], $methods['pingback.extensions.getPingbacks']);
			return $methods;
		} );

		// Remove X-Pingback HTTP header
		add_filter('wp_headers', function ($headers) {
			unset($headers['X-Pingback']);
			return $headers;
		});
	}

	/**
	 * @param $htmlSource
	 *
	 * @return mixed
	 */
	public static function removeMetaGenerators($htmlSource)
	{
		if (stripos($htmlSource, '<meta') === false) {
			return $htmlSource;
		}

		// Use DOMDocument to alter the HTML Source and Remove the tags
		$htmlSourceOriginal = $htmlSource;

		if (function_exists('libxml_use_internal_errors')
		    && function_exists('libxml_clear_errors')
		    && class_exists('DOMDocument'))
		{
			$document = new \DOMDocument();
			libxml_use_internal_errors(true);

			$document->loadHTML($htmlSource);

			$domUpdated = false;

			foreach ($document->getElementsByTagName('meta') as $tagObject) {
				$nameAttrValue = $tagObject->getAttribute('name');

				if ($nameAttrValue === 'generator') {
					$outerTag = $outerTagRegExp = trim(self::getOuterHTML($tagObject));
					$last2Chars = substr($outerTag, -2);

					if ($last2Chars === '">' || $last2Chars === "'>") {
						$tagWithoutLastChar = substr($outerTag, 0, -1);
						$outerTagRegExp = $tagWithoutLastChar.'(.*?)>';
					}

					if (strpos($outerTagRegExp, '<meta') !== false) {
						preg_match_all('#' . $outerTagRegExp . '#si', $htmlSource, $matches);

						if (isset($matches[0][0]) && ! empty($matches[0][0]) && strip_tags($matches[0][0]) === '') {
							$htmlSource = str_replace( $matches[0][0], '', $htmlSource );
						}

						if ($htmlSource !== $htmlSourceOriginal) {
							$domUpdated = true;
						}
					}
				}
			}

			libxml_clear_errors();

			if ($domUpdated) {
				return $htmlSource;
			}
		}

		// DOMDocument is not enabled. Use the RegExp instead (not as smooth, but does its job)!
		preg_match_all('#<meta(.*?)>#si', $htmlSource, $matches);

		if (isset($matches[0]) && ! empty($matches[0])) {
			foreach ($matches[0] as $metaTag) {
				if (strip_tags($metaTag) === ''
				    && (stripos($metaTag, 'name="generator"') !== false || stripos($metaTag, 'name=\'generator\'') !== false)
				) {
					$htmlSource = str_replace($metaTag, '', $htmlSource);
				}
			}
		}

		return $htmlSource;
	}

	/**
	 * @param $e
	 *
	 * @return mixed
	 */
	public static function getOuterHTML($e)
	{
		$doc = new \DOMDocument();
		$doc->appendChild( $doc->importNode( $e, true ) );

		return $doc->saveHTML();
	}
}
