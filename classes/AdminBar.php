<?php
namespace WpAssetCleanUp;

/**
 * Class AdminBar
 * @package WpAssetCleanUp
 */
class AdminBar
{
	/**
	 *
	 */
	public function __construct()
	{
		add_action( 'plugins_loaded', array( $this, 'topBar' ) );
	}

	/**
	 *
	 */
	public function topBar()
	{
		if (Menu::userCanManageAssets()) {
			add_action( 'admin_bar_menu', array( $this, 'topBarInfo' ), 999 );
		}
	}

	/**
	 * @param $wp_admin_bar
	 */
	public function topBarInfo($wp_admin_bar)
	{
		if (Main::instance()->settings['test_mode']) {
			$wp_admin_bar->add_menu(array(
				'id'    => 'wpacu-test-mode',
				'title' => WPACU_PLUGIN_TITLE.': <span class="dashicons dashicons-admin-tools"></span> <strong>TEST MODE</strong> is <strong>ENABLED SITE-WIDE</strong>',
				'href'  => admin_url('admin.php?page=' . WPACU_PLUGIN_NAME . '_settings')
			));

			$wp_admin_bar->add_menu(array(
				'parent' => 'wpacu-test-mode',
				'id'     => 'wpacu-test-mode-info',
				'title'  => 'With "Test Mode" active, any settings will be applied only for your view.',
			));

			$wp_admin_bar->add_menu(array(
				'parent' => 'wpacu-test-mode',
				'id'     => 'wpacu-test-mode-info-2',
				'title'  => 'The visitors will view the website as if the plugin is disabled. <a target="_blank" style="display:inline-block; text-decoration: underline; padding-left: 5px;" href="https://assetcleanup.com/docs/">Read more</a>',
			));
		}
	}
}
