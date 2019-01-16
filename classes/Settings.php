<?php
namespace WpAssetCleanUp;

/**
 * Class Settings
 * @package WpAssetCleanUp
 */
class Settings
{
	/**
	 * @var array
	 */
	public $settingsKeys = array(
		// Stored in 'wpassetcleanup_settings'
		'dashboard_show',
		'dom_get_type',
        'frontend_show',

		'assets_list_layout',
        'assets_list_layout_areas_status',
        'assets_list_inline_code_status',

		'input_style',

        'hide_core_files',
        'test_mode',

        // Combine loaded CSS (remaining ones after unloading the useless ones) into one file
        'combine_loaded_css',

        'disable_emojis',

		// Stored in 'wpassetcleanup_global_unload' option
        'disable_jquery_migrate',
        'disable_comment_reply'
    );

    /**
     * @var array
     */
    public $currentSettings = array();

	/**
	 * @var array
	 */
	public static $defaultSettings = array(
	    // Show the assets list within the Dashboard, while they are hidden in the front-end view
	    'dashboard_show' => '1',

	    // Direct AJAX call by default (not via WP Remote Post)
        'dom_get_type'   => 'direct',

	    'assets_list_layout'              => 'default',
	    'assets_list_layout_areas_status' => 'expanded',
	    'assets_list_inline_code_status'  => 'expanded',

	    'input_style' => 'enhanced',

	    // Starting from v1.2.8.6 (lite), WordPress core files are hidden in the assets list as a default setting
	    'hide_core_files' => '1'
    );

    /**
     * @var array
     */
    public $status = array(
        'updated' => false
    );

    /**
     *
     */
    public function init()
    {
        // This is triggered BEFORE "initAfterPluginsLoaded" from 'Main' class
        add_action('plugins_loaded', array($this, 'saveSettings'), 9);

        if (Misc::getVar('get', 'page') === WPACU_PLUGIN_ID . '_settings') {
	        add_action('wpacu_admin_notices', array($this, 'notices'));
        }
    }

	/**
	 *
	 */
	public function notices()
    {
    	$settings = $this->getAll();

    	// When all ways to manage the assets are not enabled
    	if ($settings['dashboard_show'] != 1 && $settings['frontend_show'] != 1) {
		    ?>
		    <div class="notice notice-warning">
				<p><span style="color: #ffb900;" class="dashicons dashicons-info"></span>&nbsp;<?php _e('It looks like you have both "Manage in the Dashboard?" and "Manage in the Front-end?" inactive. The plugin still works fine and any assets you have selected for unload are not loaded. However, if you want to manage the assets in any page, you need to have at least one of the view options enabled.', WPACU_PLUGIN_TEXT_DOMAIN); ?></p>
		    </div>
		    <?php
	    }

	    // After "Save changes" is clicked
        if ($this->status['updated']) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><span class="dashicons dashicons-yes"></span> <?php _e('The settings were successfully updated.', WPACU_PLUGIN_TEXT_DOMAIN); ?></p>
            </div>
            <?php
        }
    }

    /**
     *
     */
    public function saveSettings()
    {
        if (! empty($_POST) && array_key_exists('wpacu_settings_page', $_POST)) {
	        check_admin_referer('wpacu_settings_update');

            $data = Misc::getVar('post', WPACU_PLUGIN_ID . '_settings', array());
            $this->update($data);
        }
    }

    /**
     *
     */
    public function settingsPage()
    {
        $data = $this->getAll();

        foreach ($this->settingsKeys as $settingKey) {
            // Special check for plugin versions < 1.2.4.4
            if ($settingKey === 'frontend_show') {
                $data['frontend_show'] = $this->showOnFrontEnd();
            }
        }

        $globalUnloadList = Main::instance()->getGlobalUnload();

        if (in_array('jquery-migrate', $globalUnloadList['scripts'])) {
            $data['disable_jquery_migrate'] = 1;
        }

	    if (in_array('comment-reply', $globalUnloadList['scripts'])) {
		    $data['disable_comment_reply'] = 1;
	    }

        Main::instance()->parseTemplate('admin-page-settings-plugin', $data, true);
    }

    /**
     * @return bool
     */
    public function showOnFrontEnd()
    {
        $settings = $this->getAll();

        if ($settings['frontend_show'] == 1) {
            return true;
        }

        // [wpacu_lite]
        // Prior to 1.2.4.4
        if ( get_option( WPACU_PLUGIN_ID . '_frontend_show') == 1) {
            // Put it in the main settings option
            $settings = $this->getAll();
            $settings['frontend_show'] = 1;
            $this->update($settings);

            delete_option( WPACU_PLUGIN_ID . '_frontend_show');
            return true;
        }
	    // [/wpacu_lite]

        return false;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        if (! empty($this->currentSettings)) {
            return $this->currentSettings;
        }

        $settingsOption = get_option(WPACU_PLUGIN_ID . '_settings');

        // If there's already a record in the database
        if ($settingsOption !== '' && is_string($settingsOption)) {
            $settings = (array)json_decode($settingsOption);

            if (json_last_error() === JSON_ERROR_NONE) {
                // Make sure all the keys are there even if no value is attached to them
                // To avoid writing extra checks in other parts of the code and prevent PHP notice errors
                foreach ($this->settingsKeys as $settingsKey) {
                    if (! array_key_exists($settingsKey, $settings)) {
                        $settings[$settingsKey] = '';
                    }
                }

                $this->currentSettings = $settings;

                return $this->currentSettings;
            }
        }

	    // No record in the database? Set the default values
	    // That could be because no changes were done on the "Settings" page
	    // OR a full reset of the plugin (via "Tools") was performed

        $defaultSettings = self::$defaultSettings;

        foreach ($this->settingsKeys as $settingsKey) {
	        if (! array_key_exists($settingsKey, $defaultSettings)) {
		        // Keep the keys with empty values to avoid notice errors
		        $defaultSettings[$settingsKey] = '';
	        }
        }

        return $defaultSettings;
    }

    /**
     * @param $settings
     */
    public function update($settings)
    {
        // 'Combine Loaded CSS Files' activated? Make sure the cache directories and other files are there
        // /wp-content/cache/asset-cleanup/
        if ($settings['combine_loaded_css'] !== '') {
            Plugin::createCacheFoldersFiles();
        }

	    $wpacuUpdate = new Update;

	    $settingsNotNull = array();

	    foreach ($settings as $settingKey => $settingValue) {
	        if ($settingValue !== '') {
		        $settingsNotNull[$settingKey] = $settingValue;
            }
        }

	    if (json_encode(self::$defaultSettings) === json_encode($settingsNotNull)) {
	        // Do not keep a record in the database (no point of having an extra entry)
            // if the submitted values are the same as the default ones
	        delete_option(WPACU_PLUGIN_ID . '_settings');
		    $this->status['updated'] = true;
		    return;
        }

	    $disableJQueryMigrate = isset($_POST[ WPACU_PLUGIN_ID . '_global_unloads']['disable_jquery_migrate']);
	    $disableCommentReply = isset($_POST[ WPACU_PLUGIN_ID . '_global_unloads']['disable_comment_reply']);

	    /*
	     * Add element(s) to the global unload rules
	     */
        if ($disableJQueryMigrate || $disableCommentReply) {
            $unloadList = array();

	        // Add jQuery Migrate to the global unload rules
            if ($disableJQueryMigrate) {
	            $unloadList[] = 'jquery-migrate';
            }

	        // Add Comment Reply to the global unload rules
	        if ($disableCommentReply) {
		        $unloadList[] = 'comment-reply';
	        }

	        $wpacuUpdate->saveToEverywhereUnloads(array(), $unloadList);
        }

        /*
         * Remove element(s) from the global unload rules
         */
        if (! $disableJQueryMigrate || ! $disableCommentReply) {
	        $removeFromUnloadList = array();

	        // Remove jQuery Migrate from global unload rules
	        if (! $disableJQueryMigrate) {
		        $removeFromUnloadList['jquery-migrate'] = 'remove';
	        }

	        // Remove Comment Reply from global unload rules
	        if (! $disableCommentReply) {
		        $removeFromUnloadList['comment-reply'] = 'remove';
	        }

	        $wpacuUpdate->removeEverywhereUnloads(array(), $removeFromUnloadList);
        }

        update_option(WPACU_PLUGIN_ID . '_settings', json_encode($settings), 'no');
        $this->status['updated'] = true;
    }
}
