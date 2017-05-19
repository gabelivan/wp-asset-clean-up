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
        'frontend_show',
        'dashboard_show',
        'dom_get_type'
    );

    /**
     * @var array
     */
    public $currentSettings = array();

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
        add_action('admin_init', array($this, 'saveSettings'), 1);

        if (array_key_exists('page', $_GET) && $_GET['page'] === 'wpassetcleanup_settings') {
	        add_action('admin_notices', array($this, 'notices'));
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
				<p><span style="color: #ffb900;" class="dashicons dashicons-info"></span>&nbsp;<?php _e('It looks like you have both "Manage in the Dashboard?" and "Manage in the Front-end?" inactive. The plugin works fine with any settings that were applied. However, if you want to manage the assets in any page, you need to have at least one of them active.', WPACU_PLUGIN_NAME); ?></p>
		    </div>
		    <?php
	    }

	    // After "Save changes" is clicked
        if ($this->status['updated']) {
            ?>
            <div class="notice notice-success">
                <?php _e('The settings were successfully updated.', WPACU_PLUGIN_NAME); ?>
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
            $data = isset($_POST[WPACU_PLUGIN_NAME.'_settings']) ? $_POST[WPACU_PLUGIN_NAME.'_settings'] : array();

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

        Main::instance()->parseTemplate('settings-plugin', $data, true);
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

        // Prior to 1.2.4.4
        if (get_option(WPACU_PLUGIN_NAME.'_frontend_show') == 1) {
            // Put it in the main settings option
            $settings = $this->getAll();
            $settings['frontend_show'] = 1;
            $this->update($settings);

            delete_option(WPACU_PLUGIN_NAME.'_frontend_show');
            return true;
        }

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

        $settingsOption = get_option(WPACU_PLUGIN_NAME.'_settings');

        if ($settingsOption != '' && is_string($settingsOption)) {
            $settings = (array)json_decode($settingsOption);

            if (json_last_error() == JSON_ERROR_NONE) {
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

        // Keep the keys with empty values
        $list = array();

        foreach ($this->settingsKeys as $settingsKey) {
            $list[$settingsKey] = '';
        }

        return $list;
    }

    /**
     * @param $settings
     */
    public function update($settings)
    {
        update_option(WPACU_PLUGIN_NAME . '_settings', json_encode($settings), 'no');
        $this->status['updated'] = true;
    }
}
