<?php
namespace WpAssetCleanUp;

/**
 * Class Plugin
 */
class Plugin
{
	/**
	 * Plugin constructor.
	 */
	public function __construct()
	{
		register_activation_hook(WPACU_PLUGIN_FILE, array($this, 'whenActivated'));
	}

	/**
	 *
	 */
	public function whenActivated()
	{
		if (! get_option(WPACU_PLUGIN_NAME.'_settings')) {
			$defaultSettings = array(
				'dashboard_show' => 1,
				'dom_get_type'   => 'direct'
			);

            $settings = new Settings();
            $settings->update($defaultSettings);
		}
	}
}
