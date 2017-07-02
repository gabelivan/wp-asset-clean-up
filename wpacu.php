<?php
/*
 * Plugin Name: WP Asset CleanUp (Page Speed Optimizer)
 * Plugin URI: https://wordpress.org/plugins/wp-asset-clean-up/
 * Version: 1.2.5.1
 * Description: Prevent Chosen Scripts & Styles from loading in Posts/Pages that you don't need
 * Author: Gabriel Livan
 * Author URI: http://www.gabelivan.com/
*/

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

// Could be already set by the premium extension
// Keep the text domain the same
if (! defined('WPACU_PLUGIN_NAME')) {
	define('WPACU_PLUGIN_NAME', 'wpassetcleanup');
}

define('WPACU_PLUGIN_CLASSES_PATH', dirname(__FILE__).'/classes/');
define('WPACU_PLUGIN_FILE', __FILE__);
define('WPACU_PLUGIN_URL', plugins_url('', __FILE__));

// Whenever the premium extension is ready to be sold, this can be set to 'true'
define('WPACU_PLUGIN_HAS_PREMIUM_EXT', false);

// Do not load the plugin if the PHP version is below 5.3
$wpacuWrongPhp = version_compare(PHP_VERSION, '5.3.0', '<');

if (is_admin() && $wpacuWrongPhp) { // Dashboard
    add_action('admin_init', 'wpAssetCleanUpWrongPhp');
    add_action('admin_notices', 'wpAssetCleanUpWrongPhpNotice');

    /**
     * Deactivate the plugin because it has the wrong PHP version installed
     */
    function wpAssetCleanUpWrongPhp()
    {
        deactivate_plugins(plugin_basename(__FILE__));

        // The premium extension too (if any)
        deactivate_plugins('wp-asset-clean-up-pro/wpacu-pro.php');
    }

    /**
     * Print the message to the user after the plugin was deactivated
     */
    function wpAssetCleanUpWrongPhpNotice()
    {
        echo '<div class="error is-dismissible"><p>'.
             __('<strong>WP Asset CleanUp</strong> requires <span style="color: green;"><strong>5.3+</strong> PHP version</span> installed. You have <strong>'.PHP_VERSION.'</strong>. If your website is working in 5.3+ (check with your developers if you are not sure), then an upgrade is highly recommended. The plugin has been deactivated.', WPACU_PLUGIN_NAME).
             '</p></div>';

        if (array_key_exists('active', $_GET)) {
            unset($_GET['activate']);
        }
    }
} elseif ($wpacuWrongPhp) { // Front
    return;
}

require_once dirname(__FILE__).'/wpacu-load.php';
