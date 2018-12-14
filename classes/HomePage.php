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
        $this->data['nonce_name'] = WPACU_PLUGIN_ID . '_home_page_update';
        $this->data['show_on_front'] = Misc::getShowOnFront();

        $isHomePageEdit = ( Misc::getVar('get', 'page') === WPACU_PLUGIN_ID . '_home_page');

        // Only continue if we are on the plugin's homepage edit mode
        if (! $isHomePageEdit) {
            return;
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
        } else {
            // Your latest posts
            $postUrl = get_site_url();

            if (substr($postUrl, -1) !== '/') {
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
	    $wpacuNoLoadAssets = Misc::getVar('post', WPACU_PLUGIN_ID, array());
	    $wpacuHomePageUpdate = Misc::getVar('post', 'wpacu_manage_home_page_assets', false);

	    // Could Be an Empty Array as Well so just is_array() is enough to use
        if (is_array($wpacuNoLoadAssets) && ! empty($wpacuNoLoadAssets) && $wpacuHomePageUpdate) {
	        check_admin_referer($this->data['nonce_name']);

            $wpacuUpdate = new Update;
            $wpacuUpdate->updateFrontPage($wpacuNoLoadAssets);
        }

        $wpacuSettings = new Settings;
        $this->data['wpacu_settings'] = $wpacuSettings->getAll();

        Main::instance()->parseTemplate('admin-page-settings-homepage', $this->data, true);
    }
}
