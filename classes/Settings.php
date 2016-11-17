<?php
namespace WpAssetCleanUp;

/**
 * Class Settings
 * @package WpAssetCleanUp
 */
class Settings
{
    /**
     * Settings constructor.
     */
    public function __construct()
    {
        add_action('admin_init', array($this, 'registerSettings'));
    }

    /**
     *
     */
    public function registerSettings()
    {
        // Register settings
        register_setting('wpacu-plugin-settings-group', WPACU_PLUGIN_NAME.'_frontend_show');
        
        register_setting('wpacu-global-settings-group', WPACU_PLUGIN_NAME.'_global_styles_unload');
        register_setting('wpacu-global-settings-group', WPACU_PLUGIN_NAME.'_global_scripts_unload');
    }

    /**
     *
     */
    public static function settingsPage()
    {
        $data = array();
        $data['frontend_show'] = get_option(WPACU_PLUGIN_NAME.'_frontend_show');

        Main::instance()->parseTemplate('settings-plugin', $data, true);
    }
}