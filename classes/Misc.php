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
            Misc::noAdminBarLoad();
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

        $unit = trim($strThree); // remove whitespaces

        return $unit;
    }

    /**
     * @return string
     */
    public static function isHttpsSecure()
    {
        $isSecure = false;

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $isSecure = true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
            || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            // Is it behind a load balancer?
            $isSecure = true;
        }

        return $isSecure;
    }

    /**
     * @param $postId
     * @return false|mixed|string
     */
    public static function getPostUrl($postId)
    {
        // Was the home page detected?
        if (self::isHomePage()) {
            if (get_site_url() != get_home_url()) {
                $postUrl = get_home_url();
            } else {
                $postUrl = get_site_url();
            }

            return self::_filterPostUrl($postUrl);
        }

        if ($postId > 0) {
            $postUrl = get_permalink($postId);
        } else {
            $postUrl = get_site_url();

            if (substr($postUrl, -1) != '/') {
                $postUrl .= '/';
            }
        }

        return self::_filterPostUrl($postUrl);
    }

    /**
     * @param $postUrl
     * @return mixed
     */
    private static function _filterPostUrl($postUrl)
    {
        // If we are in the Dashboard on a HTTPS connection,
        // then we will make the AJAX call over HTTPS as well for the front-end
        // to avoid blocking
        if (Misc::isHttpsSecure() && strpos($postUrl, 'http://') === 0) {
            $postUrl = str_ireplace('http://', 'https://', $postUrl);
        }

        return $postUrl;
    }

    /**
     * @return mixed
     */
    public static function isHomePage()
    {
        // "Your latest posts" -> sometimes it works as is_front_page(), sometimes as is_home())
        // "A static page (select below)" -> In this case is_front_page() should work

        // Sometimes neither of these two options are selected
        // (it happens with some themes that have an incorporated page builder)
        // and is_home() tends to work fine

        // Both will be used to be sure the home page is detected
        $homePage = (is_front_page() || is_home());

        return apply_filters('wpacu_home_page', $homePage);
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
}
