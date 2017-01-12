<?php
// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

define('WPACU_PLUGIN_NAME', 'wpassetcleanup');
define('WPACU_PLUGIN_CLASSES_PATH', dirname(__FILE__).'/classes/');
define('WPACU_PLUGIN_FILE', $pluginFile);
define('WPACU_PLUGIN_URL', plugins_url('', WPACU_PLUGIN_FILE));

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
new \WpAssetCleanUp\OwnAssets;

// Add / Update / Remove Settings
$wpacuUpdate = new WpAssetCleanUp\Update;
$wpacuUpdate->init();

// Settings
new WpAssetCleanUp\Settings;

// HomePage
new WpAssetCleanUp\HomePage;

// Various functions
new WpAssetCleanUp\Misc;

// Menu
new \WpAssetCleanUp\Menu;
