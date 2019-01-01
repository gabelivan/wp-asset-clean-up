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
    	self::$slug = WPACU_PLUGIN_ID . '_getting_started';

        add_action('admin_menu', array($this, 'activeMenu'));

        if (isset($_GET['page']) && $_GET['page'] === WPACU_PLUGIN_ID . '_go_pro') {
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
            __('Asset CleanUp', WPACU_PLUGIN_TEXT_DOMAIN),
            __('Asset CleanUp', WPACU_PLUGIN_TEXT_DOMAIN),
	        self::$capability,
            self::$slug,
            array(new Info, 'gettingStarted'),
	        WPACU_PLUGIN_URL.'/assets/icons/icon-asset-cleanup.png'
        );

	    add_submenu_page(
		    self::$slug,
		    __('Settings', WPACU_PLUGIN_TEXT_DOMAIN),
		    __('Settings', WPACU_PLUGIN_TEXT_DOMAIN),
		    self::$capability,
		    WPACU_PLUGIN_ID . '_settings',
		    array(new Settings, 'settingsPage')
	    );

	    add_submenu_page(
            self::$slug,
            __('Home Page', WPACU_PLUGIN_TEXT_DOMAIN),
            __('Home Page', WPACU_PLUGIN_TEXT_DOMAIN),
	        self::$capability,
	        WPACU_PLUGIN_ID . '_home_page',
            array(new HomePage, 'page')
        );

	    add_submenu_page(
		    self::$slug,
		    __('Pages Info', WPACU_PLUGIN_TEXT_DOMAIN),
		    __('Pages Info', WPACU_PLUGIN_TEXT_DOMAIN),
		    self::$capability,
		    WPACU_PLUGIN_ID . '_pages_info',
		    array(new Info, 'pagesInfo')
	    );

	    add_submenu_page(
	        self::$slug,
            __('Bulk Unloaded', WPACU_PLUGIN_TEXT_DOMAIN),
            __('Bulk Unloaded', WPACU_PLUGIN_TEXT_DOMAIN),
	        self::$capability,
		    WPACU_PLUGIN_ID . '_bulk_unloads',
            array(new BulkUnloads, 'pageBulkUnloads')
        );

	    add_submenu_page(
	    	self::$slug,
		    __('Tools', WPACU_PLUGIN_TEXT_DOMAIN),
		    __('Tools', WPACU_PLUGIN_TEXT_DOMAIN),
		    self::$capability,
		    WPACU_PLUGIN_ID . '_tools',
		    array(new Tools, 'toolsPage')
	    );

	    // Get Help | Support Page
	    add_submenu_page(
		    self::$slug,
		    __('License', WPACU_PLUGIN_TEXT_DOMAIN),
		    __('License', WPACU_PLUGIN_TEXT_DOMAIN),
		    self::$capability,
		    WPACU_PLUGIN_ID . '_license',
		    array(new Info, 'license')
	    );

        // Get Help | Support Page
        add_submenu_page(
	        self::$slug,
            __('Help', WPACU_PLUGIN_TEXT_DOMAIN),
            __('Help', WPACU_PLUGIN_TEXT_DOMAIN),
	        self::$capability,
	        WPACU_PLUGIN_ID . '_get_help',
            array(new Info, 'help')
        );

	    // Upgrade to "Go Pro" | Redirects to sale page
	    add_submenu_page(
		    self::$slug,
		    __('Go Pro', WPACU_PLUGIN_TEXT_DOMAIN),
		    __('Go Pro', WPACU_PLUGIN_TEXT_DOMAIN) . ' <span style="font-size: 16px;" class="dashicons dashicons-star-filled"></span>',
		    self::$capability,
		    WPACU_PLUGIN_ID . '_go_pro',
		    function() {}
	    );

	    // Add "Asset CleanUp Pro" Settings Link to the main "Settings" menu within the Dashboard
	    // For easier navigation
	    $GLOBALS['submenu']['options-general.php'][] = array(
		    __('Asset CleanUp', WPACU_PLUGIN_TEXT_DOMAIN),
		    self::$capability,
		    admin_url( 'admin.php?page=' . WPACU_PLUGIN_ID . '_settings'),
		    __('Asset CleanUp', WPACU_PLUGIN_TEXT_DOMAIN),
	    );

        // Rename first item from the menu which has the same title as the menu page
        $GLOBALS['submenu'][self::$slug][0][0] = esc_attr__('Getting Started', WPACU_PLUGIN_TEXT_DOMAIN);
    }

	/**
	 * @return bool
	 */
	public static function userCanManageAssets()
	{
		return current_user_can(self::$capability) && current_user_can('activate_plugins');
	}
}
