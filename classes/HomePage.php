<?php
namespace WpAssetCleanUp;

/**
 * Class HomePage
 * @package WpAssetCleanUp
 */
class HomePage
{
    /**
     * @var array
     */
    public $data = array();

    /**
     * Settings constructor.
     */
    public function __construct()
    {
        $this->data['nonce_name'] = WPACU_PLUGIN_NAME.'_settings';
        $this->data['show_on_front'] = Misc::getShowOnFront();

        $isHomePageEdit = (isset($_GET['page']) && $_GET['page'] == WPACU_PLUGIN_NAME.'_home_page');

        // Only continue if we are on the plugin's homepage edit mode
        if (! $isHomePageEdit) {
            return '';
        }

        if ($this->data['show_on_front'] === 'page') {
            // Front page displays: A Static Page
            $this->data['page_on_front'] = get_option('page_on_front');

            if ($this->data['page_on_front']) {
                $this->data['page_on_front_title'] = get_the_title($this->data['page_on_front']);
            }

            $this->data['page_for_posts'] = get_option('page_for_posts');

            if ($this->data['page_for_posts']) {
                $this->data['page_for_posts_title'] = get_the_title($this->data['page_for_posts']);
            }
        } elseif ($this->data['show_on_front'] === 'posts') {
            // Your latest posts
            $postUrl = get_option('siteurl');

            if (substr($postUrl, -1) != '/') {
                $postUrl .= '/';
            }

            $this->data['site_url'] = $postUrl;
        }
    }

    /**
     *
     */
    public function page()
    {
        $wpacuNoLoadAssets = isset($_POST[WPACU_PLUGIN_NAME])
            ? $_POST[WPACU_PLUGIN_NAME] : array();

        $noncePost = isset($_POST[$this->data['nonce_name']])
            ? $_POST[$this->data['nonce_name']] : '';
        
        if (is_array($wpacuNoLoadAssets) && wp_verify_nonce($noncePost, $this->data['nonce_name'])) {
            $wpacuUpdate = new Update;
            $wpacuUpdate->updateFrontPage($wpacuNoLoadAssets);
        }

        $this->data['nonce_value'] = wp_create_nonce($this->data['nonce_name']);

        Main::instance()->parseTemplate('settings-home-page', $this->data, true);
    }
}
