<?php
// Exit if accessed directly
if (! defined('WPACU_PLUGIN_CLASSES_PATH')) {
    exit;
}

// Autoload Classes
function includeWpAssetCleanUpClassesAutoload($class)
{
    $namespace = 'WpAssetCleanUp';

    // continue only if the namespace is within $class
    if (strpos($class, $namespace) === false) {
        return;
    }

    $classFilter = str_replace($namespace.'\\', '', $class);

    // Can be directories such as "Helpers"
    $classFilter = str_replace('\\', '/', $classFilter);

    $pathToClass = WPACU_PLUGIN_CLASSES_PATH.$classFilter.'.php';

    if (file_exists($pathToClass)) {
        include_once $pathToClass;
    }
}

spl_autoload_register('includeWpAssetCleanUpClassesAutoload');

// Main Class
WpAssetCleanUp\Main::instance();

// Plugin's Assets (used only when you're logged in)
$wpacuOwnAssets = new \WpAssetCleanUp\OwnAssets;
$wpacuOwnAssets->init();

// Add / Update / Remove Settings
$wpacuUpdate = new WpAssetCleanUp\Update;
$wpacuUpdate->init();

// Settings
$wpacuSettings = new WpAssetCleanUp\Settings;
$wpacuSettings->init();

// Various functions
new WpAssetCleanUp\Misc;

// Menu
new \WpAssetCleanUp\Menu;

// Admin Bar (Top Area of the website when user is logged in)
new \WpAssetCleanUp\AdminBar();

/*
 * Trigger the CSS & JS combination only in the front-end view in certain conditions (not within the Dashboard)
 */
// Common functions for both CSS & JS combinations
$wpacuOptimizeCommon = new \WpAssetCleanUp\OptimiseAssets\OptimizeCommon();
$wpacuOptimizeCommon->init();

// Combine/Minify CSS Files Setup
$wpacuOptimizeCss = new \WpAssetCleanUp\OptimiseAssets\OptimizeCss();
$wpacuOptimizeCss->init();
new \WpAssetCleanUp\OptimiseAssets\MinifyCss();

// Combine/Minify JS Files Setup
$wpacuOptimizeJs = new \WpAssetCleanUp\OptimiseAssets\OptimizeJs();
$wpacuOptimizeJs->init();
new \WpAssetCleanUp\OptimiseAssets\MinifyJs();

// <head> Clean up
$cleanUp = new \WpAssetCleanUp\CleanUp();
$cleanUp->init();

if (is_admin()) {
	new \WpAssetCleanUp\Plugin;
	new \WpAssetCleanUp\Tools();
}
