<?php
namespace WpAssetCleanUp;

/**
 * Class Menu
 * @package WpAssetCleanUp
 */
class Menu
{
    /**
     * Menu constructor.
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'activeMenu'));
    }

    /**
     *
     */
    public function activeMenu()
    {
        $menuSlug  = WPACU_PLUGIN_NAME.'_settings';
        $capability = 'manage_options';

        add_menu_page(
            __('WP Asset CleanUp', WPACU_PLUGIN_NAME),
            __('WP Asset CleanUp', WPACU_PLUGIN_NAME),
            $capability,
            $menuSlug,
            array('\WpAssetCleanUp\Settings', 'settingsPage'),
            plugin_dir_url(WPACU_PLUGIN_FILE).'/assets/img/icon-clean-up.png'
        );

        add_submenu_page(
            $menuSlug,
            __('Home Page', WPACU_PLUGIN_NAME),
            __('Home Page', WPACU_PLUGIN_NAME),
            $capability,
            WPACU_PLUGIN_NAME.'_home_page',
            array(new HomePage, 'page')
        );

        add_submenu_page(
            $menuSlug,
            __('Global Rules', WPACU_PLUGIN_NAME),
            __('Global Rules', WPACU_PLUGIN_NAME),
            $capability,
            WPACU_PLUGIN_NAME.'_globals',
            array(new GlobalRules, 'page')
        );

        // Rename first item from the menu which has the same title as the menu page
        $GLOBALS['submenu'][$menuSlug][0][0] = esc_attr__('Settings', WPACU_PLUGIN_NAME);
    }
}
