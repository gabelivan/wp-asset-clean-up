<?php
/*
 * Plugin Name: Asset CleanUp: Page Speed Booster
 * Plugin URI: https://wordpress.org/plugins/wp-asset-clean-up/
 * Version: 1.3.2.2
 * Description: Unload Chosen Scripts & Styles from Posts/Pages to reduce HTTP Requests, Combine/Minify CSS/JS files
 * Author: Gabriel Livan
 * Author URI: http://gabelivan.com/
*/

define('WPACU_PLUGIN_VERSION', '1.3.2.2');

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

//removeIf(development)
	//echo 'just a test';
//endRemoveIf(development)

// Premium plugin version already exists, is it active?
// Do not load the LITE version as it's pointless
// This action is valid starting from LITE version 1.2.6.8
// From 1.0.3, the PRO version works independently (does not need anymore LITE to be active and act as a parent plugin)

// If the pro version (version above 1.0.2) was triggered first, we'll just check one of its constants
// If the lite version was triggered first, then we'll check if the pro version is active
if (   defined('WPACU_PRO_NO_LITE_NEEDED') && WPACU_PRO_NO_LITE_NEEDED !== false
    && defined('WPACU_PRO_PLUGIN_VERSION') && WPACU_PRO_PLUGIN_VERSION !== false ) {
	return;
}

define('WPACU_PLUGIN_ID',           'wpassetcleanup'); // unique prefix
define('WPACU_PLUGIN_TEXT_DOMAIN',  'wp-asset-clean-up');
define('WPACU_PLUGIN_TITLE',        'Asset CleanUp'); // a short version of the plugin name
define('WPACU_PLUGIN_FILE',         __FILE__);
define('WPACU_PLUGIN_BASE',         plugin_basename(WPACU_PLUGIN_FILE));

define('WPACU_ADMIN_PAGE_ID_START', WPACU_PLUGIN_ID . '_settings');

// Do not load the plugin if the PHP version is below 5.4
// If PHP_VERSION_ID is not defined, then PHP version is below 5.2.7, thus the plugin is not usable
$wpacuWrongPhp = ((! defined('PHP_VERSION_ID')) || (defined('PHP_VERSION_ID') && PHP_VERSION_ID < 50400));

if ($wpacuWrongPhp && is_admin()) { // Dashboard
    add_action('admin_init',    'wpAssetCleanUpWrongPhp');
    add_action('admin_notices', 'wpAssetCleanUpWrongPhpNotice');

    /**
     * Deactivate the plugin because it has the wrong PHP version installed
     */
    function wpAssetCleanUpWrongPhp()
    {
        deactivate_plugins(WPACU_PLUGIN_BASE);

        // The premium extension too (if any)
        deactivate_plugins('wp-asset-clean-up-pro/wpacu-pro.php');
	    deactivate_plugins('wp-asset-clean-up-pro/wpacu.php');
    }

    /**
     * Print the message to the user after the plugin was deactivated
     */
    function wpAssetCleanUpWrongPhpNotice()
    {
        echo '<div class="error is-dismissible"><p>'.
             __('<strong>'.WPACU_PLUGIN_TITLE.'</strong> requires <span style="color: green;"><strong>5.4+</strong> PHP version</span> installed. You have <strong>'.PHP_VERSION.'</strong>. If you\'re website is compatible with PHP 7+ (e.g. you can check with your developers or contact the hosting company), it\'s strongly recommended to upgrade for a better performance. The plugin has been deactivated.', WPACU_PLUGIN_TEXT_DOMAIN) .
             '</p></div>';

        if (array_key_exists('active', $_GET)) {
            unset($_GET['activate']);
        }
    }
} elseif ($wpacuWrongPhp) { // Front
    return;
}

define('WPACU_PLUGIN_DIR',          __DIR__);
define('WPACU_PLUGIN_CLASSES_PATH', WPACU_PLUGIN_DIR.'/classes/');
define('WPACU_PLUGIN_URL',          plugins_url('', WPACU_PLUGIN_FILE));

// Upgrade to Pro Sales Page
define('WPACU_PLUGIN_GO_PRO_URL',   'https://gabelivan.com/items/wp-asset-cleanup-pro/');

// Global Values
define('WPACU_LOAD_ASSETS_REQ_KEY', WPACU_PLUGIN_ID . '_load');

require_once WPACU_PLUGIN_DIR.'/freemius-load.php';
require_once WPACU_PLUGIN_DIR.'/wpacu-load.php';
