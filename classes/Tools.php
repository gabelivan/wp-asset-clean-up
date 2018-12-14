<?php
namespace WpAssetCleanUp;

/**
 * Class Tools
 * @package WpAssetCleanUp
 */
class Tools
{
	/**
	 * @var
	 */
	public $resetChoice;

	/**
	 * Tools constructor.
	 */
	public function __construct()
	{
		add_action('plugins_loaded', function() {
			if ( array_key_exists( 'wpacu-tools-reset', $_POST ) && $_POST['wpacu-tools-reset'] ) {
				$this->doReset();
			}
		});
	}

	/**
	 *
	 */
	public function toolsPage()
	{
		$data = array();
		Main::instance()->parseTemplate('admin-page-tools', $data, true);
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

		$resetStatus = false;

		if ($wpacuResetValue === 'reset_everything') {
			// `usermeta` and `termmeta` might have traces from the Pro version (if ever used)
			foreach (array('postmeta', 'usermeta', 'termmeta') as $tableBaseName) {
				$sqlQuery = <<<SQL
DELETE FROM `{$wpdb->prefix}{$tableBaseName}` WHERE meta_key LIKE '_wpassetcleanup_%'
SQL;
				$resetStatus = $wpdb->query($sqlQuery);
			}

			$sqlQuery = <<<SQL
DELETE FROM `{$wpdb->prefix}options` WHERE option_name LIKE 'wpassetcleanup_%'
SQL;
			$wpdb->query($sqlQuery);
		} elseif ($wpacuResetValue === 'reset_settings') {
			$sqlQuery = <<<SQL
DELETE FROM `{$wpdb->prefix}options` WHERE option_name='wpassetcleanup_settings'
SQL;
			$resetStatus = $wpdb->query($sqlQuery);
        }

		// Also make 'jQuery Migrate' and 'Comment Reply' core files to load again
		// As they were enabled (not unloaded) in the default settings
		if ($resetStatus !== false) {
		    $wpacuUpdate = new Update();
			$wpacuUpdate->removeEverywhereUnloads(
                array(),
                array('jquery-migrate' => 'remove', 'comment-reply' => 'remove')
            );
        }

		add_action('wpacu_admin_notices', array($this, 'resetDone'));
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
			$msg = __('Everything was reset (including settings, individual &amp; bulk unloads, load exceptions) to the same point it was when you first activated the plugin', WPACU_PLUGIN_TEXT_DOMAIN);
		}
		?>
		<div class="updated notice wpacu-notice is-dismissible">
			<p><span class="dashicons dashicons-yes"></span> <?php echo $msg; ?></p>
		</div>
		<?php
	}
}
