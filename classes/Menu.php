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
            __('WP Asset Clean Up', WPACU_PLUGIN_NAME),
            $capability,
            $menuSlug,
            array(new Settings, 'settingsPage'),
            'dashicons-filter'
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
            __('Bulk Unloads', WPACU_PLUGIN_NAME),
            __('Bulk Unloads', WPACU_PLUGIN_NAME),
            $capability,
            WPACU_PLUGIN_NAME.'_bulk_unloads',
            array(new BulkUnloads, 'pageBulkUnloads')
        );

        if (WPACU_PLUGIN_HAS_PREMIUM_EXT !== false) {
            add_submenu_page(
                $menuSlug,
                __('Custom Unload Rules', WPACU_PLUGIN_NAME),
                __('Custom Unload Rules', WPACU_PLUGIN_NAME),
                $capability,
                WPACU_PLUGIN_NAME . '_advanced_rules',
                array(new AdvancedRules, 'page')
            );
        }

        // Get Help | Support Page
        add_submenu_page(
            $menuSlug,
            __('Get Help', WPACU_PLUGIN_NAME),
            __('Get Help', WPACU_PLUGIN_NAME),
            $capability,
            WPACU_PLUGIN_NAME.'_get_help',
            array(new GetHelp, 'page')
        );

        // Rename first item from the menu which has the same title as the menu page
        $GLOBALS['submenu'][$menuSlug][0][0] = esc_attr__('Settings', WPACU_PLUGIN_NAME);
    }
}
