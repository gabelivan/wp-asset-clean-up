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
		$topTitle = WPACU_PLUGIN_TITLE;

		if (Main::instance()->settings['test_mode']) {
			$topTitle .= '&nbsp; <span class="dashicons dashicons-admin-tools"></span> <strong>TEST MODE</strong> is <strong>ON</strong>';
		}

		$goBackToCurrentUrl = '&_wp_http_referer=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) );

		$wp_admin_bar->add_menu(array(
			'id'    => 'assetcleanup-parent',
			'title' => $topTitle,
			'href'  => admin_url( 'admin.php?page=' . WPACU_PLUGIN_ID . '_settings')
		));

		$wp_admin_bar->add_menu(array(
			'parent' => 'assetcleanup-parent',
			'id'     => 'assetcleanup-settings',
			'title'  => 'Settings',
			'href'   => admin_url( 'admin.php?page=' . WPACU_PLUGIN_ID . '_settings')
		));

		if (Main::instance()->settings['combine_loaded_css']) {
			$wp_admin_bar->add_menu( array(
				'parent' => 'assetcleanup-parent',
				'id'     => 'assetcleanup-clear-all-css-cache',
				'title'  => 'Clear Combined CSS Cache',
				'href'   => wp_nonce_url( admin_url( 'admin-post.php?action=assetcleanup_clear_assets_cache' . $goBackToCurrentUrl ),
					'purge_css_cache' )
			) );
		}

		// Only trigger in the front-end view
		if (! is_admin()) {
			if ( ! Misc::isHomePage() ) {
				// Not on the home page
				$homepageManageAssetsHref = Main::instance()->settings['frontend_show']
					? get_site_url().'#wpacu_wrap_assets'
					: admin_url( 'admin.php?page=' . WPACU_PLUGIN_ID . '_home_page' );

				$wp_admin_bar->add_menu(array(
					'parent' => 'assetcleanup-parent',
					'id'     => 'assetcleanup-homepage',
					'title'  => 'Manage Homepage Assets',
					'href'   => $homepageManageAssetsHref
				));
			} else {
				// On the home page
				// Front-end view is disabled! Go to Dashboard link
				if ( ! Main::instance()->settings['frontend_show'] ) {
					$wp_admin_bar->add_menu( array(
						'parent' => 'assetcleanup-parent',
						'id'     => 'assetcleanup-homepage',
						'title'  => 'Manage Page Assets',
						'href'   => admin_url( 'admin.php?page=' . WPACU_PLUGIN_ID . '_home_page' )
					) );
				}
			}
		}

		if (! is_admin() && Main::instance()->settings['frontend_show']) {
			$wp_admin_bar->add_menu(array(
				'parent' => 'assetcleanup-parent',
				'id'     => 'assetcleanup-jump-to-assets-list',
				'title'  => 'Manage Page Assets',
				'href'   => '#wpacu_wrap_assets'
			));
		}

		$wp_admin_bar->add_menu(array(
			'parent' => 'assetcleanup-parent',
			'id'     => 'assetcleanup-bulk-unloaded',
			'title'  => 'Bulk Unloaded',
			'href'   => admin_url( 'admin.php?page=' . WPACU_PLUGIN_ID . '_bulk_unloads')
		));

		$wp_admin_bar->add_menu(array(
			'parent' => 'assetcleanup-parent',
			'id'     => 'assetcleanup-support-forum',
			'title'  => 'Support Forum',
			'href'   => 'https://wordpress.org/support/plugin/wp-asset-clean-up',
			'meta'   => array('target' => '_blank')
		));

		/*
		if (Main::instance()->settings['test_mode']) {
			$wp_admin_bar->add_menu(array(
				'parent' => 'assetcleanup-parent',
				'id'     => 'assetcleanup-test-mode-info',
				'title'  => 'With "Test Mode" on, anything will be applied only for your view.',
			));

			$wp_admin_bar->add_menu(array(
				'parent' => 'assetcleanup-parent',
				'id'     => 'assetcleanup-test-mode-info-2',
				'title'  => 'The visitors will see the pages as if the plugin is disabled. <a target="_blank" style="display:inline-block; text-decoration: underline; padding-left: 5px;" href="https://assetcleanup.com/docs/">More</a>',
			));
		}
		*/
	}
}
