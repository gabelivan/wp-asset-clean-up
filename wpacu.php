<?php
/*
 * Plugin Name: WP Asset CleanUp
 * Plugin URI: https://wordpress.org/plugins/wp-asset-clean-up/
 * Version: 1.2.4.4
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

// Do not load the plugin if the PHP version is below 5.3
$wpacuWrongPhp = version_compare(PHP_VERSION, '5.3.0', '<');

if (is_admin() && $wpacuWrongPhp) { // Dashboard
    wp_die(
        __('This plugin requires <span style="color: green;"><strong>5.3+</strong> PHP version</span> installed. You have <strong>'.PHP_VERSION.'</strong>. If your website is working in 5.3+ (check with your developers if you are not sure), then an upgrade is highly recommended.', WPACU_PLUGIN_NAME),
        __('Plugin Activation Error', WPACU_PLUGIN_NAME),
        array('response' => 200, 'back_link' => true)
    );
} elseif ($wpacuWrongPhp) { // Front
    return;
}

require_once dirname(__FILE__).'/wpacu-load.php';
