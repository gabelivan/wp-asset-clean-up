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
        if (self::isHomePage()) {
            return get_option('siteurl');
        }

        if ($postId > 0) {
            $postUrl = get_permalink($postId);
        } else {
            $postUrl = get_option('siteurl');

            if (substr($postUrl, -1) != '/') {
                $postUrl .= '/';
            }
        }

        // If we are in the Dashboard on a HTTPS connection,
        // then we will make the AJAX call over HTTPS as well for the front-end
        // to avoid blocking
        $https = Misc::isHttpsSecure();

        if ($https && strpos($postUrl, 'http://') === 0) {
            $postUrl = str_ireplace('http://', 'https://', $postUrl);
        }

        return $postUrl;
    }

    /**
     * @return mixed
     */
    public static function isHomePage()
    {
        $homePage = false;

        if (self::$showOnFront === 'page') {
            $homePage = is_front_page();
        } elseif (self::$showOnFront === 'posts') {
            $homePage = is_home();
        }

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
}
