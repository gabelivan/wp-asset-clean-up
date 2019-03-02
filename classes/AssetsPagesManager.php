<?php
namespace WpAssetCleanUp;

/**
 * Class AssetsPagesManager
 * @package WpAssetCleanUp
 */
class AssetsPagesManager
{
    /**
     * @var array
     */
    public $data = array();

	/**
	 * AssetsPagesManager constructor.
	 */
	public function __construct()
    {
	    $this->data = array('for' => 'homepage'); // default

	    if (isset($_GET['wpacu_for']) && $_GET['wpacu_for'] !== '') {
		    $this->data['for'] = sanitize_text_field($_GET['wpacu_for']);
	    }

	    if (isset($_GET['page'])) {
		    $this->data['page'] = $_GET['page'];
	    }

	    if ($this->data['for'] === 'homepage') {
		    $wpacuSettings = new Settings;
		    $this->data['wpacu_settings'] = $wpacuSettings->getAll();

		    $this->homepage();
	    }
    }

	/**
	 *
	 */
    public function homepage()
    {
        $this->data['nonce_name'] = WPACU_PLUGIN_ID . '_home_page_update';
        $this->data['show_on_front'] = Misc::getShowOnFront();

        $isHomePageEdit = ( Misc::getVar('get', 'page') === WPACU_PLUGIN_ID . '_assets_manager'
                            && $this->data['for'] === 'homepage' );

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

        if (! empty($_POST)) {
	        // Update action?
	        $wpacuNoLoadAssets   = Misc::getVar( 'post', WPACU_PLUGIN_ID, array() );
	        $wpacuHomePageUpdate = Misc::getVar( 'post', 'wpacu_manage_home_page_assets', false );

	        // Could Be an Empty Array as Well so just is_array() is enough to use
	        if ( is_array( $wpacuNoLoadAssets ) && $wpacuHomePageUpdate ) {
		        check_admin_referer( $this->data['nonce_name'] );

		        $wpacuUpdate = new Update;
		        $wpacuUpdate->updateFrontPage( $wpacuNoLoadAssets );
	        }
        }
    }

	/**
	 *
	 */
	public function page()
    {
	    Main::instance()->parseTemplate('admin-page-assets-manager', $this->data, true);
    }
}
