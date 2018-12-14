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
		// [wpacu_lite]
		// Admin footer text: Ask the user to review the plugin
		add_filter('admin_footer_text', array($this, 'adminFooter'), 1, 1);
		// [/wpacu_lite]

		// Show "Settings" and "Go Pro" as plugin action links
		add_filter('plugin_action_links_'.WPACU_PLUGIN_BASE, array($this, 'actionLinks'));
	}

	/**
	 * @param $links
	 *
	 * @return mixed
	 */
	public function actionLinks($links)
	{
		$links['settings'] = '<a href="admin.php?page=' . WPACU_PLUGIN_ID . '_settings">' . __('Settings', WPACU_PLUGIN_TEXT_DOMAIN) . '</a>';

		// [wpacu_lite]
		$allPlugins = get_plugins();

		// If the pro version is not installed (active or not), show the upgrade link
		if (! array_key_exists('wp-asset-clean-up-pro/wpacu.php', $allPlugins)) {
			$links['go_pro'] = '<a target="_blank" style="font-weight: bold;" href="'.WPACU_PLUGIN_GO_PRO_URL.'">Go Pro</a>';
		}
		// [/wpacu_lite]

		return $links;
	}

	// [wpacu_lite]
	/**
	 * @param $text
	 *
	 * @return string
	 */
	public function adminFooter($text)
	{
		if (isset($_GET['page']) && strpos($_GET['page'], WPACU_PLUGIN_ID) !== false) {
			$reviewUrl = 'https://wordpress.org/support/plugin/wp-asset-clean-up/reviews/?filter=5#new-post';
			$text = 'Thank you for using '.WPACU_PLUGIN_TITLE.' v'.WPACU_PLUGIN_VERSION.') <span class="dashicons dashicons-smiley"></span> &nbsp;&nbsp; If you like it, please <a target="_blank" href="'.$reviewUrl.'"><strong>rate</strong> '.WPACU_PLUGIN_TITLE.'</a> <a target="_blank" href="'.$reviewUrl.'"><span class="dashicons dashicons-wpacu dashicons-star-filled"></span><span class="dashicons dashicons-wpacu dashicons-star-filled"></span><span class="dashicons dashicons-wpacu dashicons-star-filled"></span><span class="dashicons dashicons-wpacu dashicons-star-filled"></span><span class="dashicons dashicons-wpacu dashicons-star-filled"></span></a> on WordPress.org to help me spread the word to the community.';
		}

		return $text;
	}
	// [/wpacu_lite]
}
