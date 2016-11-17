<?php
namespace WpAssetCleanUp;

/**
 * Class OwnAssets
 *
 * These are plugin's own assets and they are used only when you're logged and do not show in the list for unload
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
     * OwnAssets constructor.
     */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'stylesAndScriptsForAdmin'));
        add_action('wp_enqueue_scripts', array($this, 'stylesAndScriptsForPublic'));
    }

    /**
     *
     */
    public function stylesAndScriptsForAdmin()
    {
        global $post;

        $page = (isset($_GET['page'])) ? $_GET['page'] : '';
        $getPostId = (isset($_GET['post'])) ? (int)$_GET['post'] : '';

        // Only load the plugin's assets when they are needed
        // This an example of assets that are correctly loaded in WordPress
        if (isset($post->ID)) {
            $this->loadPluginAssets = true;
        }

        if ($getPostId > 0) {
            $this->loadPluginAssets = true;
        }

        if (in_array($page, array(WPACU_PLUGIN_NAME.'_home_page', WPACU_PLUGIN_NAME.'_globals'))) {
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
        $this->enqueuePublicStyles();
        $this->enqueuePublicScripts();
    }

    /**
     *
     */
    private function enqueueAdminStyles()
    {
        wp_enqueue_style(WPACU_PLUGIN_NAME . '-style', plugins_url('/assets/style.min.css', WPACU_PLUGIN_FILE));
        wp_enqueue_style(WPACU_PLUGIN_NAME . '-icheck-square-red', plugins_url('/assets/icheck/skins/square/red.css', WPACU_PLUGIN_FILE));
    }

    /**
     *
     */
    private function enqueueAdminScripts()
    {
        global $post, $pagenow;

        $page = (isset($_GET['page'])) ? $_GET['page'] : '';

        $getPostId = (isset($_GET['post'])
            && isset($_GET['action'])
            && $_GET['action'] === 'edit'
            && $pagenow == 'post.php')
            ? (int)$_GET['post'] : '';

        $postId = (isset($post->ID)) ? $post->ID : 0;

        if ($getPostId > 0 && $getPostId != $postId) {
            $postId = $getPostId;
        }

        if ($page == WPACU_PLUGIN_NAME.'_home_page' || $postId < 1) {
            $postId = 0; // for home page
        }

        // Not home page (posts list)? See if the individual post is published to continue
        if ($postId > 0) {
            $postStatus = get_post_status($postId);

            if (! $postStatus) {
                return;
            }

            // Only for Published Posts
            if ($postStatus != 'publish') {
                return;
            }
        }

        wp_register_script(WPACU_PLUGIN_NAME . '-script', plugins_url('/assets/script.min.js', WPACU_PLUGIN_FILE), array('jquery'), '1.1');

        // It can also be the front page URL
        $postUrl = Misc::getPostUrl($postId);

        $this->fetchUrl = $postUrl;

        wp_localize_script(
            WPACU_PLUGIN_NAME . '-script',
            'wpacu_object',
            array(
                'plugin_name' => WPACU_PLUGIN_NAME,
                'ajax_url' => admin_url('admin-ajax.php'),
                'post_id' => $postId,
                'post_url' => $postUrl
            )
        );

        wp_enqueue_script(WPACU_PLUGIN_NAME . '-icheck', plugins_url('/assets/icheck/icheck.min.js', WPACU_PLUGIN_FILE), array('jquery'));
        wp_enqueue_script(WPACU_PLUGIN_NAME . '-script');
    }

    /**
     *
     */
    private function enqueuePublicStyles()
    {
        if (Main::instance()->frontendShow && current_user_can('manage_options') && !isset($_POST[WPACU_PLUGIN_NAME.'_load'])) {
            wp_enqueue_style(WPACU_PLUGIN_NAME . '-style', plugins_url('/assets/style.min.css', WPACU_PLUGIN_FILE));
            wp_enqueue_style(WPACU_PLUGIN_NAME . '-icheck-square-red', plugins_url('/assets/icheck/skins/square/red.css', WPACU_PLUGIN_FILE));
        }
    }

    /**
     *
     */
    public function enqueuePublicScripts()
    {
        if (Main::instance()->frontendShow && current_user_can('manage_options') && !isset($_POST[WPACU_PLUGIN_NAME.'_load'])) {
            wp_enqueue_script(WPACU_PLUGIN_NAME . '-icheck', plugins_url('/assets/icheck/icheck.min.js', WPACU_PLUGIN_FILE), array('jquery'));
            wp_enqueue_script(WPACU_PLUGIN_NAME . '-script', plugins_url('/assets/script.min.js', WPACU_PLUGIN_FILE), array('jquery'), '1.1');
        }
    }
}
