<?php
namespace WpAssetCleanUp;

/**
 * Class Plugin
 * @package WpAssetCleanUp
 */
class Plugin
{
	/**
	 * Plugin constructor.
	 */
	public function __construct()
	{
		register_activation_hook(WPACU_PLUGIN_FILE, array($this, 'whenActivated'));
		add_action('admin_init', array($this, 'redirectToStartingPage'));

		// [wpacu_lite]
		// Admin footer text: Ask the user to review the plugin
		add_filter('admin_footer_text', array($this, 'adminFooter'), 1, 1);
		// [/wpacu_lite]

		// Show "Settings" and "Go Pro" as plugin action links
		add_filter('plugin_action_links_'.WPACU_PLUGIN_BASE, array($this, 'actionLinks'));
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

	/**
	 *
	 */
	public function whenActivated()
	{
		// Is the plugin activated for the first time?
		// Prepare for the redirection to the WPACU_ADMIN_PAGE_ID_START plugin page
		if (! get_option(WPACU_PLUGIN_ID.'_do_activation_redirect_first_time')) {
			add_option(WPACU_PLUGIN_ID.'_do_activation_redirect_first_time', 1, 'no');
			set_transient(WPACU_PLUGIN_ID . '_redirect_after_activation', 1, 15);
		}

		/**
		 * /wp-content/cache/asset-cleanup/
		 * /wp-content/cache/asset-cleanup/index.php
		 * /wp-content/cache/asset-cleanup/.htaccess
		 *
		 * /wp-content/cache/asset-cleanup/css/
		 * /wp-content/cache/asset-cleanup/css/index.php
		 * /wp-content/cache/asset-cleanup/css/logged-in/
		 * /wp-content/cache/asset-cleanup/css/logged-in/index.php
		 */
		self::createCacheFoldersFiles();
	}

	/**
	 *
	 */
	public static function createCacheFoldersFiles()
	{
		$cacheCssDir = WP_CONTENT_DIR . OptimizeCss::$relPathCssCacheDir;

		$emptyPhpFileContents = <<<TEXT
<?php
// Silence is golden.
TEXT;

		$htAccessContents = <<<HTACCESS
<IfModule mod_autoindex.c>
Options -Indexes
</IfModule>
HTACCESS;


		if (! is_dir($cacheCssDir)) {
			mkdir($cacheCssDir, 0755, true);
		}

		if (! is_file($cacheCssDir . 'index.php')) {
			// /wp-content/cache/asset-cleanup/cache/css/index.php
			file_put_contents( $cacheCssDir . 'index.php', $emptyPhpFileContents );
		}

		if (! is_dir($cacheCssDir . 'logged-in')) {
			mkdir( $cacheCssDir . 'logged-in', 0755 );
		}

		if (! is_file($cacheCssDir . 'logged-in/index.php')) {
			// /wp-content/cache/asset-cleanup/cache/css/logged-in/index.html
			file_put_contents( $cacheCssDir . 'logged-in/index.php', $emptyPhpFileContents );
		}

		$htAccessFilePath = dirname($cacheCssDir) . '/.htaccess';

		if (! is_file($htAccessFilePath)) {
			// /wp-content/cache/asset-cleanup/.htaccess
			file_put_contents( $htAccessFilePath, $htAccessContents );
		}

		if (! is_file(dirname($cacheCssDir) . '/index.php')) {
			// /wp-content/cache/asset-cleanup/index.php
			file_put_contents( dirname( $cacheCssDir ) . '/index.php', $emptyPhpFileContents );
		}
	}

	/**
	 *
	 */
	public function redirectToStartingPage()
	{
		if (get_transient(WPACU_PLUGIN_ID . '_redirect_after_activation')) {
			// Remove it as only one redirect is needed (first time the plugin is activated)
			delete_transient(WPACU_PLUGIN_ID . '_redirect_after_activation');
			
			// Do the 'first activation time' redirection
			wp_redirect(admin_url('admin.php?page=' . WPACU_ADMIN_PAGE_ID_START));
			exit();
		}
	}

	/**
	 * @param $links
	 *
	 * @return mixed
	 */
	public function actionLinks($links)
	{
		$links['getting_started'] = '<a href="admin.php?page=' . WPACU_PLUGIN_ID . '_getting_started">' . __('Getting Started', WPACU_PLUGIN_TEXT_DOMAIN) . '</a>';
		$links['settings']        = '<a href="admin.php?page=' . WPACU_PLUGIN_ID . '_settings">'        . __('Settings',        WPACU_PLUGIN_TEXT_DOMAIN) . '</a>';

		// [wpacu_lite]
		$allPlugins = get_plugins();

		// If the pro version is not installed (active or not), show the upgrade link
		if (! array_key_exists('wp-asset-clean-up-pro/wpacu.php', $allPlugins)) {
			$links['go_pro'] = '<a target="_blank" style="font-weight: bold;" href="'.WPACU_PLUGIN_GO_PRO_URL.'">Go Pro</a>';
		}
		// [/wpacu_lite]

		return $links;
	}
}
