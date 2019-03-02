<?php
namespace WpAssetCleanUp;

/**
 * Class Tools
 * @package WpAssetCleanUp
 */
class Tools
{
	/**
	 * @var string
	 */
	public $wpacuFor = 'reset';

	/**
	 * @var
	 */
	public $resetChoice;

	/**
	 * @var bool
	 */
	public $licenseDataRemoved = false;

	/**
	 * @var array
	 */
	public $data = array();

	/**
	 * Tools constructor.
	 */
	public function __construct()
	{
		$this->wpacuFor = Misc::getVar('request', 'wpacu_for', $this->wpacuFor);

		add_action('plugins_loaded', array($this, 'afterPluginsLoaded'));
		add_action('admin_init',     array($this, 'onAdminInit'));
	}

	/**
	 *
	 */
	public function afterPluginsLoaded()
    {
	    if ( array_key_exists( 'wpacu-tools-reset', $_POST ) && $_POST['wpacu-tools-reset'] && is_admin() ) {
		    $this->doReset();
	    }
    }

	/**
	 *
	 */
	public function onAdminInit()
    {
	    if ( array_key_exists( 'wpacu-get-system-info', $_POST ) && $_POST['wpacu-get-system-info'] ) {
		    $this->downloadSystemInfo();
	    }
    }

	/**
	 *
	 */
	public function toolsPage()
	{
		$this->data['for'] = $this->wpacuFor;

		if ($this->data['for'] === 'system_info') {
		    $this->data['system_info'] = $this->getSystemInfo();
        }

		Main::instance()->parseTemplate('admin-page-tools', $this->data, true);
	}

	/**
	 * @return string
	 */
	public function getSystemInfo()
    {
	    global $wpdb;

	    $return = '### Begin System Info ###' . "\n";

	    $return .= "\n" . '# Site Info' . "\n";
	    $return .= 'Site URL:                  ' . site_url() . "\n";
	    $return .= 'Home URL:                  ' . home_url() . "\n";
	    $return .= 'Multisite:                 ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

        $return .= "\n" . '# Asset CleanUp Configuration '. "\n";

        $settingsClass = new Settings();
	    $settings = $settingsClass->getAll();

        $return .= 'Manage in the Dashboard:             '. (($settings['dashboard_show'] == 1) ? 'Yes ('.$settings['dom_get_type'].')' : 'No') . "\n";
        $return .= 'Manage in the Front-end:             '. (($settings['frontend_show'] == 1) ? 'Yes' : 'No') ."\n";
	    $return .= 'Input Fields Style:                  '. ucfirst($settings['input_style'])."\n";
	    $return .= 'Hide WP Files (from managing):       '. (($settings['hide_core_files'] == 1) ? 'Yes' : 'No') . "\n";
	    $return .= 'Enable "Test Mode"?                  '. (($settings['test_mode'] == 1) ? 'Yes' : 'No') . "\n";
	    $return .= 'Disable Emojis?                      '. (($settings['disable_emojis'] == 1) ? 'Yes' : 'No') . "\n";

	    $return .= 'Disable jQuery Migrate (site-wide)?  '. (($settings['disable_jquery_migrate'] == 1) ? 'Yes' : 'No') . "\n";
	    $return .= 'Disable Comment Reply (site-wide)?   '. (($settings['disable_comment_reply'] == 1) ? 'Yes' : 'No') . "\n";

	    // WordPress configuration.
	    // Get theme info.
	    $theme_data = wp_get_theme();
	    $theme      = $theme_data->Name . ' ' . $theme_data->Version;

	    $return .= "\n" . '# WordPress Configuration' . "\n";
	    $return .= 'Version:                   ' . get_bloginfo( 'version' ) . "\n";
	    $return .= 'Language:                  ' . ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ) . "\n";
	    $return .= 'Permalink Structure:       ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
	    $return .= 'Active Theme:              ' . $theme . "\n";
	    $return .= 'Show On Front:             ' . get_option( 'show_on_front' ) . "\n";

	    // Only show page specs if front page is set to 'page'.
	    if ( get_option( 'show_on_front' ) === 'page' ) {
		    $front_page_id = get_option( 'page_on_front' );
		    $blog_page_id  = get_option( 'page_for_posts' );

		    $return .= 'Page On Front:             ' . ( 0 != $front_page_id ? get_the_title( $front_page_id ) . ' (ID: ' . $front_page_id . ')' : 'Unset' ) . "\n";
		    $return .= 'Page For Posts:            ' . ( 0 != $blog_page_id ? get_the_title( $blog_page_id ) . ' (ID: ' . $blog_page_id . ')' : 'Unset' ) . "\n";
	    }

	    $return .= 'ABSPATH:                   ' . ABSPATH . "\n";
	    $return .= 'WP_DEBUG:                  ' . ( defined( 'WP_DEBUG' ) ? (WP_DEBUG ? 'Enabled' : 'Disabled') : 'Not set' ) . "\n";
	    $return .= 'Memory Limit:              ' . WP_MEMORY_LIMIT . "\n";

	    $return .= "\n" . '# WordPress Uploads/Constants' . "\n";
	    $return .= 'WP_CONTENT_DIR:            ' . ( defined( 'WP_CONTENT_DIR' ) ? (WP_CONTENT_DIR ? WP_CONTENT_DIR : 'Disabled') : 'Not set' ) . "\n";
	    $return .= 'WP_CONTENT_URL:            ' . ( defined( 'WP_CONTENT_URL' ) ? (WP_CONTENT_URL ? WP_CONTENT_URL : 'Disabled') : 'Not set' ) . "\n";
	    $return .= 'UPLOADS:                   ' . ( defined( 'UPLOADS' ) ? (UPLOADS ? UPLOADS : 'Disabled') : 'Not set' ) . "\n";

	    $uploads_dir = wp_upload_dir();

	    $return .= 'wp_uploads_dir() path:     ' . $uploads_dir['path'] . "\n";
	    $return .= 'wp_uploads_dir() url:      ' . $uploads_dir['url'] . "\n";
	    $return .= 'wp_uploads_dir() basedir:  ' . $uploads_dir['basedir'] . "\n";
	    $return .= 'wp_uploads_dir() baseurl:  ' . $uploads_dir['baseurl'] . "\n";

	    // Get plugins that have an update.
	    $updates = get_plugin_updates();

	    // Must-use plugins.
	    // NOTE: MU plugins can't show updates!
	    $muplugins = get_mu_plugins();
	    if ( ! empty( $muplugins ) && count( $muplugins ) > 0 ) {
		    $return .= "\n" . '# Must-Use Plugins ("mu-plugins" directory)' . "\n";

		    foreach ( $muplugins as $plugin => $plugin_data ) {
			    $return .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
		    }
	    }

	    // WordPress active plugins.
	    $return .= "\n" . '# Active Plugins ("plugins" directory)' . "\n";

	    $plugins        = get_plugins();
	    $active_plugins = get_option( 'active_plugins', array() );

	    foreach ( $plugins as $plugin_path => $plugin ) {
		    if ( ! in_array( $plugin_path, $active_plugins, true ) ) {
			    continue;
		    }
		    $update  = array_key_exists($plugin_path, $updates) ? ' (new version available - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
		    $return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
	    }

	    // WordPress inactive plugins.
	    $return .= "\n" . '# Inactive Plugins ("plugins" directory)' . "\n";

	    foreach ( $plugins as $plugin_path => $plugin ) {
		    if ( in_array( $plugin_path, $active_plugins, true ) ) {
			    continue;
		    }
		    $update  = array_key_exists($plugin_path, $updates) ? ' (new version available - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
		    $return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
	    }

	    if ( is_multisite() ) {
		    // WordPress Multisite active plugins.
		    $return .= "\n" . '# Network Active Plugins' . "\n";

		    $plugins        = wp_get_active_network_plugins();
		    $active_plugins = get_site_option( 'active_sitewide_plugins', array() );

		    foreach ( $plugins as $plugin_path ) {
			    $plugin_base = plugin_basename( $plugin_path );
			    if ( ! array_key_exists( $plugin_base, $active_plugins ) ) {
				    continue;
			    }
			    $update  = array_key_exists($plugin_path, $updates) ? ' (new version available - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
			    $plugin  = get_plugin_data( $plugin_path );
			    $return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		    }
	    }

	    // Server configuration (really just versions).
	    $return .= "\n" . '# Webserver Configuration' . "\n";
	    $return .= 'PHP Version:              ' . PHP_VERSION . "\n";
	    $return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
	    $return .= 'Webserver Info:           ' . $_SERVER['SERVER_SOFTWARE'] . "\n";

	    // PHP important configuration taken from php.ini
	    $return .= "\n" . '# PHP Configuration' . "\n";
	    $return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
	    $return .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
	    $return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
	    $return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
	    $return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
	    $return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
	    $return .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (php.ini value: ' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";

	    // PHP extensions and such.
	    $return .= "\n" . '# PHP Extensions' . "\n";
	    $return .= 'cURL:                     ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . "\n";
	    $return .= 'fsockopen:                ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . "\n";
	    $return .= 'SOAP Client:              ' . ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) . "\n";
	    $return .= 'Suhosin:                  ' . ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) . "\n";

	    // Session stuff.
	    $return .= "\n" . '# Session Configuration' . "\n";
	    $return .= 'Session:                  ' . ( isset( $_SESSION ) ? 'Enabled' : 'Disabled' ) . "\n";

	    // The rest of this is only relevant if session is enabled.
	    if ( isset( $_SESSION ) ) {
		    $return .= 'Session Name:             ' . esc_html( ini_get( 'session.name' ) ) . "\n";
		    $return .= 'Cookie Path:              ' . esc_html( ini_get( 'session.cookie_path' ) ) . "\n";
		    $return .= 'Save Path:                ' . esc_html( ini_get( 'session.save_path' ) ) . "\n";
		    $return .= 'Use Cookies:              ' . ( ini_get( 'session.use_cookies' ) ? 'On' : 'Off' ) . "\n";
		    $return .= 'Use Only Cookies:         ' . ( ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off' ) . "\n";
	    }

	    $return .= "\n" . '### End System Info ###';

	    return $return;
    }

	/**
	 *
	 */
	public function downloadSystemInfo()
    {
	    if (! Menu::userCanManageAssets()) {
		    exit();
	    }

	    \check_admin_referer('wpacu_get_system_info');

	    $date = date('j-M-Y');
	    $host = parse_url(site_url(), PHP_URL_HOST);

	    header('Content-type: text/plain');
	    header('Content-Disposition: attachment; filename="asset-cleanup-system-info-'.$host.'-'.$date.'.txt"');

	    echo $this->getSystemInfo();
	    exit();
    }

	/**
	 *
	 */
	public function doReset()
	{
		// Several security checks before proceeding with the chosen action
		if ( ! (isset($_POST['wpacu-tools-reset']) && $_POST['wpacu-tools-reset']) ) {
			exit();
		}

		\check_admin_referer('wpacu_tools_reset');

		$wpacuResetValue = isset($_POST['wpacu-reset']) ? $_POST['wpacu-reset'] : false;

		if (! $wpacuResetValue) {
			exit('Error: Field not found, the action is not valid!');
		}

		// Has to be confirmed
		$wpacuConfirmedValue = isset($_POST['wpacu-action-confirmed']) ? $_POST['wpacu-action-confirmed'] : false;

		if ($wpacuConfirmedValue !== 'yes') {
			exit('Error: Action needs to be confirmed.');
		}

		if (! Menu::userCanManageAssets()) {
			exit();
		}

		global $wpdb;

		$this->resetChoice = $wpacuResetValue;

		if ($wpacuResetValue === 'reset_everything') {
			// `usermeta` and `termmeta` might have traces from the Pro version (if ever used)
			foreach (array('postmeta', 'usermeta', 'termmeta') as $tableBaseName) {
				$sqlQuery = <<<SQL
DELETE FROM `{$wpdb->prefix}{$tableBaseName}` WHERE meta_key LIKE '_wpassetcleanup_%'
SQL;
				$wpdb->query($sqlQuery);
			}

			$sqlQuery = <<<SQL
DELETE FROM `{$wpdb->prefix}options`
WHERE option_name LIKE 'wpassetcleanup_%'
                  AND option_name NOT IN('wpassetcleanup_pro_license_key', 'wpassetcleanup_pro_license_status')
SQL;
			$wpdb->query($sqlQuery);

			delete_option(WPACU_PLUGIN_ID.'_do_activation_redirect_first_time');

			// Remove the license data?
			if (Misc::getVar('post', 'wpacu-remove-license-data') !== '') {
				delete_option(WPACU_PLUGIN_ID . '_pro_license_key');
				delete_option(WPACU_PLUGIN_ID . '_pro_license_status');
				$this->licenseDataRemoved = true;
			}

			// Remove Asset CleanUp's cache transients
            $this->clearAllCacheTransients();

			// Refers to the plugins' icons shown when viewing assets list by location is enabled
			delete_transient('wpacu_active_plugins_icons');
		} elseif ($wpacuResetValue === 'reset_settings') {
			$sqlQuery = <<<SQL
DELETE FROM `{$wpdb->prefix}options` WHERE option_name='wpassetcleanup_settings'
SQL;
			$wpdb->query($sqlQuery);
        }

		// Also make 'jQuery Migrate' and 'Comment Reply' core files to load again
		// As they were enabled (not unloaded) in the default settings
        $wpacuUpdate = new Update();
        $wpacuUpdate->removeEverywhereUnloads(
            array(),
            array('jquery-migrate' => 'remove', 'comment-reply' => 'remove')
        );

		add_action('wpacu_admin_notices', array($this, 'resetDone'));
	}

	/**
	 * Remove Asset CleanUp's Cache Transients
	 */
	public function clearAllCacheTransients()
    {
        global $wpdb;

	    // Remove Asset CleanUp's cache transients
	    $transientLikes = array(
		    '_transient_wpacu_css_',
		    '_transient_wpacu_js_'
	    );

	    $transientLikesSql = '';

	    foreach ($transientLikes as $transientLike) {
		    $transientLikesSql .= " option_name LIKE '%".$transientLike."%' OR ";
	    }

	    $transientLikesSql = rtrim($transientLikesSql, ' OR ');

	    $sqlQuery = <<<SQL
SELECT option_name FROM `{$wpdb->prefix}options` WHERE {$transientLikesSql}
SQL;
	    $transientsToClear = $wpdb->get_col($sqlQuery);

	    foreach ($transientsToClear as $transientToClear) {
	        $transientNameToClear = str_replace('_transient_', '', $transientToClear);
		    delete_transient($transientNameToClear);
	    }
    }

	/**
	 *
	 */
	public function resetDone()
	{
		$msg = '';

		if ($this->resetChoice === 'reset_settings') {
			$msg = __('All the settings were reset to their default values.', WPACU_PLUGIN_TEXT_DOMAIN);
		} elseif ($this->resetChoice === 'reset_everything') {
			$msg = __('Everything was reset (including settings, individual &amp; bulk unloads, load exceptions) to the same point it was when you first activated the plugin.', WPACU_PLUGIN_TEXT_DOMAIN);

			if ($this->licenseDataRemoved) {
				$msg .= '<span id="wpacu-license-data-removed-msg">'.__('Any license data was also removed, as you requested.').'</span>';
			}
		}
		?>
		<div class="updated notice wpacu-notice wpacu-reset-notice is-dismissible">
			<p><span class="dashicons dashicons-yes"></span> <?php echo $msg; ?></p>
		</div>
		<?php
	}
}
