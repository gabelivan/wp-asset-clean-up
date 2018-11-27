<?php
namespace WpAssetCleanUp;

/**
 * Class Misc
 * contains various common functions that are used by the plugin
 * @package WpAssetCleanUp
 */
class Misc
{
    /**
     * Misc constructor.
     */
    public function __construct()
    {
        if (isset($_REQUEST['wpacuNoAdminBar'])) {
            self::noAdminBarLoad();
        }
    }

    /**
     * @var
     */
    public static $showOnFront;

    /**
     * @param $string
     * @param $start
     * @param $end
     * @return string
     */
    public static function extractBetween($string, $start, $end)
    {
        $pos = stripos($string, $start);

        $str = substr($string, $pos);

        $strTwo = substr($str, strlen($start));

        $secondPos = stripos($strTwo, $end);

        $strThree = substr($strTwo, 0, $secondPos);

        return trim($strThree); // remove whitespaces;
    }

	/**
	 * @return string
	 */
	public static function isHttpsSecure()
	{
		$isSecure = false;

		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
			$isSecure = true;
		} elseif (
			( ! empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' )
			|| ( ! empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on' )
		) {
			// Is it behind a load balancer?
			$isSecure = true;
		}

		return $isSecure;
	}

    /**
     * @param $postId
     * @return false|mixed|string
     */
    public static function getPageUrl($postId)
    {
        // Was the home page detected?
        if (self::isHomePage()) {
            if (get_site_url() !== get_home_url()) {
                $pageUrl = get_home_url();
            } else {
                $pageUrl = get_site_url();
            }

            return self::_filterPageUrl($pageUrl);
        }

	    // It's singular page: post, page, custom post type (e.g. 'product' from WooCommerce)
        if ($postId > 0) {
            return self::_filterPageUrl(get_permalink($postId));
        }

        // For Pro Version (Dashboard view): category link, tag link, custom taxonomy etc.
        if (is_admin() && Main::instance()->wpacuProEnabled()) {
        	$wpacuOwnAssets = new OwnAssets();

        	if ($wpacuOwnAssets->isTaxonomyEditPage()) {
		        $current_screen = \get_current_screen();

		        $term = isset($_GET['tag_ID']) ? (int)$_GET['tag_ID'] : false;
		        $taxonomy = $current_screen->taxonomy;

		        return get_term_link($term, $taxonomy);
	        }
        }

        // If it's not a singular page, nor the home page, continue...
	    // It could be: Archive page (e.g. author, category, tag, date, custom taxonomy), Search page, 404 page etc.
	    global $wp;

        $permalinkStructure = get_option('permalink_structure');

        if ($permalinkStructure) {
		    $pageUrl = home_url($wp->request);
	    } else {
		    $pageUrl = home_url($_SERVER['REQUEST_URI']);
	    }

        if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
	        list( $cleanRequestUri ) = explode( '?', $_SERVER['REQUEST_URI'] );
        } else {
	        $cleanRequestUri = $_SERVER['REQUEST_URI'];
        }

        if (substr($cleanRequestUri, -1) === '/') {
        	$pageUrl .= '/';
        }

        return self::_filterPageUrl($pageUrl);
    }

    /**
     * @param $postUrl
     * @return mixed
     */
    private static function _filterPageUrl($postUrl)
    {
        // If we are in the Dashboard on a HTTPS connection,
        // then we will make the AJAX call over HTTPS as well for the front-end
        // to avoid blocking
        if (self::isHttpsSecure() && strpos($postUrl, 'http://') === 0) {
            $postUrl = str_ireplace('http://', 'https://', $postUrl);
        }

        return $postUrl;
    }

    /**
     * @return mixed
     */
    public static function isHomePage()
    {
	    // Docs: https://codex.wordpress.org/Conditional_Tags

	    // "Your latest posts" -> sometimes it works as is_front_page(), sometimes as is_home())
	    // "A static page (select below)" -> In this case is_front_page() should work

	    // Sometimes neither of these two options are selected
	    // (it happens with some themes that have an incorporated page builder)
	    // and is_home() tends to work fine

	    // Both will be used to be sure the home page is detected

	    // VARIOUS SCENARIOS for "Your homepage displays" option from Settings -> Reading

	    // 1) "Your latest posts" is selected
	    if (self::getShowOnFront() === 'posts' && is_front_page()) {
	    	// Default homepage
	    	return true;
	    }

	    // 2) "A static page (select below)" is selected

	    // Note: Either "Homepage:" or "Posts page:" need to have a value set
	    // Otherwise, it will default to "Your latest posts", the other choice from "Your homepage displays"

	    if (self::getShowOnFront() === 'page') {
			$pageOnFront = get_option('page_on_front');

		    // "Homepage:" has a value
			if ($pageOnFront > 0 && is_front_page()) {
				// Static Homepage
				return true;
			}

		    // "Homepage:" has no value
			if (! $pageOnFront && self::isBlogPage()) {
				// Blog page
				return true;
			}

		    // Another scenario is when both 'Homepage:' and 'Posts page:' have values
		    // If we are on the blog page (which is "Posts page:" value), then it will return false
		    // As it's not the main page of the website
		    // e.g. Main page: www.yoursite.com - Blog page: www.yoursite.com/blog/
	    }

	    // Some WordPress themes such as "Extra" have their own custom value
	    $return = ( ( (self::getShowOnFront() !== '') || (self::getShowOnFront() === 'layout') )
	         &&
		    ((is_home() || self::isBlogPage()) || self::isRootUrl())
	    );

	    return $return;
    }

	/**
	 * @return bool
	 */
	public static function isRootUrl()
    {
    	$siteUrl = get_bloginfo('url');

	    $urlPath = parse_url($siteUrl, PHP_URL_PATH);
	    $requestURI = $_SERVER['REQUEST_URI'];

	    $urlPathNoForwardSlash = $urlPath;
	    $requestURINoForwardSlash = $requestURI;

	    if (substr($urlPath, -1) === '/') {
	    	$urlPathNoForwardSlash = substr($urlPath, 0, -1);
	    }

	    if (substr($requestURI, -1) === '/') {
		    $requestURINoForwardSlash = substr($requestURI, 0, -1);
	    }

	    return ($urlPathNoForwardSlash === $requestURINoForwardSlash);
    }

	/**
	 * @return bool
	 */
	public static function isBlogPage()
    {
    	return (is_home() && !is_front_page());
    }

    /**
     * @return mixed
     */
    public static function getShowOnFront()
    {
        if (! self::$showOnFront) {
            self::$showOnFront = get_option('show_on_front');
        }

        return self::$showOnFront;
    }

    /**
     *
     */
    public static function noAdminBarLoad()
    {
        add_filter('show_admin_bar', '__return_false');
    }

    /**
     * @return bool
     */
    public static function isWooCommerceActive()
    {
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }

	/**
	 * @param $requestMethod
	 * @param $key
	 * @param mixed $defaultValue
	 *
	 * @return mixed
	 */
	public static function getVar($requestMethod, $key, $defaultValue = '')
    {
	    if ($requestMethod === 'get' && $key && isset($_GET[$key])) {
		    return $_GET[$key];
	    }

		if ($requestMethod === 'post' && $key && isset($_POST[$key])) {
			return $_POST[$key];
		}

	    if ($requestMethod === 'request' && $key && isset($_REQUEST[$key])) {
		    return $_REQUEST[$key];
	    }

	    return $defaultValue;
    }
}
