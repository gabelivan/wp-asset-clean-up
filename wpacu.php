<?php
/*
 * Plugin Name: WP Asset CleanUp
 * Plugin URI: http://www.bitrepository.com/remove-unused-scripts-styles-wordpress-pages.html
 * Version: 1.2.4.3
 * Description: Prevent Chosen Scripts & Styles from loading in Posts/Pages that you don't need
 * Author: Gabriel Livan
 * Author URI: http://www.gabelivan.com/
*/

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

// Do not load the plugin if the PHP version is below 5.3
$wpacuWrongPhp = version_compare(PHP_VERSION, '5.3.0', '<');

if (is_admin() && $wpacuWrongPhp) { // Dashboard
    wp_die(
        __('This plugin requires <span style="color: green;"><strong>5.3+</strong> PHP version</span> installed. You have <strong>'.PHP_VERSION.'</strong>. If your website is working in 5.3+ (check with your developers if you are not sure), then an upgrade is highly recommended.', AFP_TEXT_DOMAIN),
        __('Plugin Activation Error', AFP_TEXT_DOMAIN),
        array('response' => 200, 'back_link' => true)
    );
} elseif ($wpacuWrongPhp) { // Front
    return;
}

$pluginFile = __FILE__;

require_once dirname(__FILE__).'/wpacu-load.php';
