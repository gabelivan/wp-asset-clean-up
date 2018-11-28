<?php
namespace WpAssetCleanUp;

/**
 * Class Menu
 * @package WpAssetCleanUp
 */
class Menu
{
	/**
	 * @var string
	 */
	static private $capability = 'administrator';

	/**
	 * @var string
	 */
	static private $slug;

    /**
     * Menu constructor.
     */
    public function __construct()
    {
    	self::$slug = WPACU_PLUGIN_NAME . '_settings';

        add_action('admin_menu', array($this, 'activeMenu'));

	    if (isset($_GET['page']) && $_GET['page'] === WPACU_PLUGIN_NAME.'_feature_request') {
		    header('Location: '.WPACU_PLUGIN_FEATURE_REQUEST_URL.'?utm_source=plugin_feature_request_from_lite');
		    exit();
	    }

        if (isset($_GET['page']) && $_GET['page'] === WPACU_PLUGIN_NAME.'_go_pro') {
        	header('Location: '.WPACU_PLUGIN_GO_PRO_URL.'?utm_source=plugin_go_pro');
        	exit();
        }
    }

    /**
     *
     */
    public function activeMenu()
    {
	    // User should be of 'administrator' role and allowed to activate plugins
	    if (! self::userCanManageAssets()) {
		    return;
	    }

        add_menu_page(
            __('Asset CleanUp', WPACU_PLUGIN_NAME),
            __('Asset CleanUp', WPACU_PLUGIN_NAME),
	        self::$capability,
            self::$slug,
            array(new Settings, 'settingsPage'),
	        WPACU_PLUGIN_URL.'/assets/icons/icon-asset-cleanup.png'
        );

        add_submenu_page(
            self::$slug,
            __('Home Page', WPACU_PLUGIN_NAME),
            __('Home Page', WPACU_PLUGIN_NAME),
	        self::$capability,
            WPACU_PLUGIN_NAME.'_home_page',
            array(new HomePage, 'page')
        );

	    add_submenu_page(
		    self::$slug,
		    __('Pages Info', WPACU_PLUGIN_NAME),
		    __('Pages Info', WPACU_PLUGIN_NAME),
		    self::$capability,
		    WPACU_PLUGIN_NAME.'_pages_info',
		    array(new Info, 'pagesInfo')
	    );

	    add_submenu_page(
	        self::$slug,
            __('Bulk Unloaded', WPACU_PLUGIN_NAME),
            __('Bulk Unloaded', WPACU_PLUGIN_NAME),
	        self::$capability,
            WPACU_PLUGIN_NAME.'_bulk_unloads',
            array(new BulkUnloads, 'pageBulkUnloads')
        );

	    // Get Help | Support Page
	    add_submenu_page(
		    self::$slug,
		    __('License', WPACU_PLUGIN_NAME),
		    __('License', WPACU_PLUGIN_NAME),
		    self::$capability,
		    WPACU_PLUGIN_NAME.'_license',
		    array(new Info, 'license')
	    );

        // Get Help | Support Page
        add_submenu_page(
	        self::$slug,
            __('Help', WPACU_PLUGIN_NAME),
            __('Help', WPACU_PLUGIN_NAME),
	        self::$capability,
            WPACU_PLUGIN_NAME.'_get_help',
            array(new Info, 'help')
        );

	    // Feature Request | Redirects to feature request form
	    add_submenu_page(
		    self::$slug,
		    __('Feature Request', WPACU_PLUGIN_NAME),
		    __('Feature Request', WPACU_PLUGIN_NAME).' <span style="font-size: 16px; line-height: 22px; margin-left: -3px;" class="dashicons dashicons-plus"></span>',
		    self::$capability,
		    WPACU_PLUGIN_NAME.'_feature_request',
		    function() {}
	    );

	    // Upgrade to "Go Pro" | Redirects to sale page
	    add_submenu_page(
		    self::$slug,
		    __('Go Pro', WPACU_PLUGIN_NAME),
		    __('Go Pro', WPACU_PLUGIN_NAME).' <span style="font-size: 16px;" class="dashicons dashicons-star-filled"></span>',
		    self::$capability,
		    WPACU_PLUGIN_NAME.'_go_pro',
		    function() {}
	    );

        // Rename first item from the menu which has the same title as the menu page
        $GLOBALS['submenu'][self::$slug][0][0] = esc_attr__('Settings', WPACU_PLUGIN_NAME);
    }

	/**
	 * @return bool
	 */
	public static function userCanManageAssets()
	{
		return current_user_can(self::$capability) && current_user_can('activate_plugins');
	}
}
