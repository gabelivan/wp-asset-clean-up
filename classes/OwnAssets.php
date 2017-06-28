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

        if (Main::instance()->frontendShow && (! isset($_POST[WPACU_PLUGIN_NAME.'_load']))) {
            add_action('wp_enqueue_scripts', array($this, 'stylesAndScriptsForPublic'));
        }
    }

    /**
     *
     */
    public function stylesAndScriptsForAdmin()
    {
        global $post;

        if (! current_user_can('manage_options')) {
            return;
        }

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

        if (in_array($page, array(WPACU_PLUGIN_NAME.'_settings', WPACU_PLUGIN_NAME.'_home_page', WPACU_PLUGIN_NAME.'_bulk_unloads', WPACU_PLUGIN_NAME.'_get_help'))) {
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
        if (! current_user_can('manage_options')) {
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
        wp_enqueue_style(WPACU_PLUGIN_NAME . '-style', plugins_url($styleRelPath, WPACU_PLUGIN_FILE), array(), $this->_assetVer($styleRelPath));
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

        $scriptRelPath = '/assets/script.min.js';

        wp_register_script(
            WPACU_PLUGIN_NAME . '-script',
            plugins_url($scriptRelPath, WPACU_PLUGIN_FILE),
            array('jquery'),
            $this->_assetVer($scriptRelPath)
        );

        // It can also be the front page URL
        $postUrl = Misc::getPostUrl($postId);

        wp_localize_script(
            WPACU_PLUGIN_NAME . '-script',
            'wpacu_object',
            array(
                'plugin_name'  => WPACU_PLUGIN_NAME,
                'dom_get_type' => Main::$domGetType,
                'start_del'    => Main::START_DEL,
                'end_del'      => Main::END_DEL,
                'ajax_url'     => admin_url('admin-ajax.php'),
                'post_id'      => $postId,
                'post_url'     => $postUrl
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
        $styleRelPath = '/assets/style.min.css';
        wp_enqueue_style(WPACU_PLUGIN_NAME . '-style', plugins_url($styleRelPath, WPACU_PLUGIN_FILE), array(), $this->_assetVer($styleRelPath));
        wp_enqueue_style(WPACU_PLUGIN_NAME . '-icheck-square-red', plugins_url('/assets/icheck/skins/square/red.css', WPACU_PLUGIN_FILE));
    }

    /**
     *
     */
    public function enqueuePublicScripts()
    {
        $scriptRelPath = '/assets/script.min.js';
        wp_enqueue_script(WPACU_PLUGIN_NAME . '-icheck', plugins_url('/assets/icheck/icheck.min.js', WPACU_PLUGIN_FILE), array('jquery'));
        wp_enqueue_script(WPACU_PLUGIN_NAME . '-script', plugins_url($scriptRelPath, WPACU_PLUGIN_FILE), array('jquery'), $this->_assetVer($scriptRelPath));
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

    public function codeablePostProject()
    {
        ?>
        <script>
            (function(c,o,d,e,a,b,l){c['CodeableObject']=a;c[a]=c[a]||function(){
                    (c[a].q=c[a].q||[]).push(arguments)},c[a].l=1*new Date();b=o.createElement(d),
                l=o.getElementsByTagName(d)[0];b.async=1;b.src=e;l.parentNode.insertBefore(b,l)
            })(window,document,'script','https://referoo.co/assets/form.js','cdbl');

            cdbl('shortcode', '0JTXB');
            cdbl('render', 'wpacu-get-quote');
        </script>
        <?php
    }
}
