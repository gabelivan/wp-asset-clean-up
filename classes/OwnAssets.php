<?php
namespace WpAssetCleanUp;

/**
 * Class OwnAssets
 *
 * These are plugin's own assets (CSS, JS etc.) and they are used only when you're logged in and do not show in the list for unload
 *
 * @package WpAssetCleanUp
 */
class OwnAssets
{
    /**
     * @var bool
     */
    public $loadPluginAssets = false; // default

	/**
	 * @var bool
	 */
	public $isTaxonomyEditPage = false;

	/**
	 *
	 */
	public function init()
    {
        add_action('admin_enqueue_scripts', array($this, 'stylesAndScriptsForAdmin'));
        add_action('wp_enqueue_scripts', array($this, 'stylesAndScriptsForPublic'));

        add_action('admin_head', array($this, 'inlineCode'));
        add_action('wp_head', array($this, 'inlineCode'));
    }

	/**
	 *
	 */
	public function inlineCode()
    {
        ?>
        <style type="text/css">
            .menu-top.toplevel_page_wpassetcleanup_settings .wp-menu-image > img { width: 26px; position: absolute; left: 8px; top: -4px; }

            <?php if (is_admin_bar_showing()) { ?>
                #wp-admin-bar-wpacu-test-mode span.dashicons { width: 15px; height: 15px; font-family: 'Dashicons', Arial, "Times New Roman", "Bitstream Charter", Times, serif; }
                #wp-admin-bar-wpacu-test-mode > a:first-child strong { font-weight: bolder; color: #76f203; }
                #wp-admin-bar-wpacu-test-mode > a:first-child:hover { color: #00b9eb; }
                #wp-admin-bar-wpacu-test-mode > a:first-child:hover strong { color: #00b9eb; }

                /* Add some spacing below the last text */
                #wp-admin-bar-wpacu-test-mode-info-2 { padding-bottom: 8px !important; }
            <?php } ?>
        </style>
        <?php
    }

    /**
     *
     */
    public function stylesAndScriptsForAdmin()
    {
        global $post;

        if (! Menu::userCanManageAssets()) {
            return;
        }

        $page = isset($_GET['page']) ? $_GET['page'] : '';
        $getPostId = isset($_GET['post']) ? (int)$_GET['post'] : '';

        // Only load the plugin's assets when they are needed
        // This an example of assets that are correctly loaded in WordPress
        if (isset($post->ID)) {
            $this->loadPluginAssets = true;
        }

        if ($getPostId > 0) {
            $this->loadPluginAssets = true;
        }

        if ( strpos($page, WPACU_PLUGIN_ID) === 0) {
            $this->loadPluginAssets = true;
        }

	    if ($this->isTaxonomyEditPage()) {
		    $this->loadPluginAssets = true;
	    }

        if (! $this->loadPluginAssets) {
            return;
        }

        $this->enqueueAdminStyles();
        $this->enqueueAdminScripts();
    }

    /**
     *
     */
    public function stylesAndScriptsForPublic()
    {
        // Do not print it when an AJAX call is made from the Dashboard
        if (isset($_POST[WPACU_LOAD_ASSETS_REQ_KEY])) {
            return;
        }

        // Only for the administrator with the right permission
        if (! Menu::userCanManageAssets()) {
            return;
        }

	    // Is the Admin Bar not showing and "Manage in the Front-end" option is not enabled in the plugin's "Settings"?
	    // In this case, there's no point in loading the assets below
        if (! is_admin_bar_showing() && ! Main::instance()->settings['frontend_show']) {
            return;
        }

        $this->enqueuePublicStyles();
        $this->enqueuePublicScripts();
    }

    /**
     *
     */
    private function enqueueAdminStyles()
    {
        $styleRelPath = '/assets/style.min.css';
        wp_enqueue_style( WPACU_PLUGIN_ID . '-style', plugins_url($styleRelPath, WPACU_PLUGIN_FILE), array(), $this->_assetVer($styleRelPath));
    }

    /**
     *
     */
    private function enqueueAdminScripts()
    {
        global $post, $pagenow;

        $page = isset($_GET['page']) ? $_GET['page'] : '';

        $getPostId = (isset($_GET['post'], $_GET['action']) && $_GET['action'] === 'edit' && $pagenow === 'post.php') ? (int)$_GET['post'] : '';

        $postId = isset($post->ID) ? $post->ID : 0;

        if ($getPostId > 0 && $getPostId != $postId) {
            $postId = $getPostId;
        }

        if ( $page === WPACU_PLUGIN_ID . '_home_page' || $postId < 1) {
            $postId = 0; // for home page
        }

        // Not home page (posts list) nor Taxonomy Edit Page? Does it have a post ID?
        // See if the individual post is published to continue
        if ($postId > 0 && (! $this->isTaxonomyEditPage())) {
            $postStatus = get_post_status($postId);

            if (! $postStatus) {
                return;
            }

            // Only for Published Posts
            if ($postStatus !== 'publish') {
                return;
            }
        }

        $scriptRelPath = '/assets/script.min.js';

        wp_register_script(
	        WPACU_PLUGIN_ID . '-script',
            plugins_url($scriptRelPath, WPACU_PLUGIN_FILE),
            array('jquery'),
            $this->_assetVer($scriptRelPath)
        );

        // It can also be the front page URL
        $pageUrl = Misc::getPageUrl($postId);

        $wpacuObjectData = array(
	        'plugin_name'  => WPACU_PLUGIN_ID,
	        'dom_get_type' => Main::$domGetType,
	        'start_del'    => Main::START_DEL,
	        'end_del'      => Main::END_DEL,
	        'ajax_url'     => admin_url('admin-ajax.php'),
	        'post_id'      => $postId, // if any
	        'page_url'     => $pageUrl // post, page, custom post type, homepage etc.
        );

        // [wpacu_lite]
        $submitTicketLink = 'https://wordpress.org/support/plugin/wp-asset-clean-up';
        // [/wpacu_lite]

        $wpacuObjectData['ajax_direct_fetch_error'] = <<<HTML
<div class="ajax-direct-call-error-area">
    <p class="note"><strong>Note:</strong> The checked URL returned an error when fetching the assets via AJAX call. This could be because of a firewall that is blocking the AJAX call, a redirect loop or an error in the script that is retrieving the output which could be due to an incompatibility between the plugin and the WordPress setup you are using.</p>
    <p>Here is the response from the call:</p>

    <table>
        <tr>
            <td width="135"><strong>Status Code Error:</strong></td>
            <td><span class="error-code">{wpacu_status_code_error}</span> * for more information about client and server errors, <a target="_blank" href="https://en.wikipedia.org/wiki/List_of_HTTP_status_codes">check this link</a></td>
        </tr>
        <tr>
            <td valign="top"><strong>Suggestion:</strong></td>
            <td>Select "WP Remote Post" as a method of retrieving the assets from the "Settings" page. If that doesn't fix the issue, just use "Manage in Front-end" option which should always work and <a target="_blank" href="{$submitTicketLink}">submit a ticket</a> about your problem.</td>
        </tr>
        <tr>
            <td><strong>Output:</strong></td>
            <td>{wpacu_output}</td>
        </tr>
    </table>
</div>
HTML;

	    $wpacuObjectData['jquery_migration_disable_confirm_msg'] = __(
            'Make sure to properly test your website if you unload the jQuery migration library.'."\n\n".
            'In some cases, due to old jQuery code triggered from plugins or the theme, unloading this migration library could cause those scripts not to function anymore and break some of the front-end functionality.'."\n\n".
            'If you are not sure about whether activating this option is right or not, it is better to leave it as it is (to be loaded by default) and consult with a developer.'."\n\n".
            'Confirm this action to enable the unloading or cancel to leave it loaded by default.', WPACU_PLUGIN_TEXT_DOMAIN);

	    $wpacuObjectData['comment_reply_disable_confirm_msg'] = __(
		    'This is worth disabling if you are NOT using the default WordPress comment system (e.g. you are using the website for business purposes, to showcase your products and you are not using it as a blog where people leave comments to your posts).'."\n\n".
		    'If you are not sure about whether activating this option is right or not, it is better to leave it as it is (to be loaded by default).'."\n\n".
		    'Confirm this action to enable the unloading or cancel to leave it loaded by default.', WPACU_PLUGIN_TEXT_DOMAIN);

	    $wpacuObjectData['reset_settings_confirm_msg'] = __(
		    'Are you sure you want to reset the settings to their default values?'."\n\n".'This is an irreversible action.'."\n\n".'Please confirm to continue or "Cancel" to abort it',
            WPACU_PLUGIN_TEXT_DOMAIN
        );

	    $wpacuObjectData['reset_everything_confirm_msg'] = __(
		    'Are you sure you want to reset everything (settings, unloads, load exceptions etc.) to the same point it was when you first activated the plugin?'."\n\n".
            'This is an irreversible action.'."\n\n".
            'Please confirm to continue or "Cancel" to abort it.',
		    WPACU_PLUGIN_TEXT_DOMAIN
	    );

        wp_localize_script(
	        WPACU_PLUGIN_ID . '-script',
            'wpacu_object',
            apply_filters('wpacu_object_data', $wpacuObjectData)
        );

        wp_enqueue_script( WPACU_PLUGIN_ID . '-script');
    }

    /**
     *
     */
    private function enqueuePublicStyles()
    {
        $styleRelPath = '/assets/style.min.css';
        wp_enqueue_style( WPACU_PLUGIN_ID . '-style', plugins_url($styleRelPath, WPACU_PLUGIN_FILE), array(), $this->_assetVer($styleRelPath));
    }

    /**
     *
     */
    public function enqueuePublicScripts()
    {
        $scriptRelPath = '/assets/script.min.js';
        wp_enqueue_script( WPACU_PLUGIN_ID . '-script', plugins_url($scriptRelPath, WPACU_PLUGIN_FILE), array('jquery'), $this->_assetVer($scriptRelPath));
    }

    /**
     * @param $relativePath
     * @return bool|false|int|string
     */
    private function _assetVer($relativePath)
    {
        $assetVer = @filemtime(dirname(WPACU_PLUGIN_FILE) . $relativePath);

        if (! $assetVer) {
            $assetVer = date('dmYHi');
        }

        return $assetVer;
    }

	/**
	 * @return bool
	 */
	public function isTaxonomyEditPage()
    {
        if ((!$this->isTaxonomyEditPage)
            && Main::instance()->wpacuProEnabled()
            && class_exists('\\WpAssetCleanUpPro\\MainPro')) {
            $mainPro = new \WpAssetCleanUpPro\MainPro();
	        $this->isTaxonomyEditPage = $mainPro->isTaxonomyEditPage();
        }

        return $this->isTaxonomyEditPage;
    }
}
