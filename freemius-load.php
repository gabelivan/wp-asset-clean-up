<?php
if ( ! function_exists( 'wpassetcleanup_fs' )
     && ! class_exists( 'Freemius' )
     && is_admin() ) {

	// Create a helper function for easy SDK access.
	function wpassetcleanup_fs() {
		global $wpassetcleanup_fs;

		if ( ! isset( $wpassetcleanup_fs ) ) {
			// Include Freemius SDK.
			require_once __DIR__ . '/freemius/start.php';

			$wpassetcleanup_fs = fs_dynamic_init( array (
				'id'             => '2951',
				'slug'           => WPACU_PLUGIN_TEXT_DOMAIN,
				'type'           => 'plugin',
				'public_key'     => 'pk_70ecc6600cb03b5168150b4c99257',
				'is_premium'     => false,
				'has_addons'     => false,
				'has_paid_plans' => false,
				'menu'           => array(
					'slug'           => WPACU_PLUGIN_ID . '_settings',
					'override_exact' => true,
					'account'        => false,
					'contact'        => false,
					'support'        => true,
				),
			) );
		}

		return $wpassetcleanup_fs;
	}

	// Init Freemius.
	wpassetcleanup_fs();

	// Signal that SDK was initiated.
	do_action('wpassetcleanup_fs_loaded');

	function wpassetcleanup_fs_settings_url() {
		return admin_url('admin.php?page='.WPACU_PLUGIN_ID.'_settings');
	}

	wpassetcleanup_fs()->add_filter('connect_url', WPACU_PLUGIN_ID.'_fs_settings_url');
	wpassetcleanup_fs()->add_filter('after_skip_url', WPACU_PLUGIN_ID.'_fs_settings_url');
	wpassetcleanup_fs()->add_filter('after_connect_url', WPACU_PLUGIN_ID.'_fs_settings_url');
	wpassetcleanup_fs()->add_filter('after_pending_connect_url', WPACU_PLUGIN_ID.'_fs_settings_url');
}
