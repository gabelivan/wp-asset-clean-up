<?php
namespace WpAssetCleanUp;

/**
 *
 * Class BulkUnloads
 * @package WpAssetCleanUp
 */
class BulkUnloads
{
    /**
     * @var string
     */
    public $wpacuFor = 'everywhere';

    /**
     * @var string
     */
    public $wpacuPostType = 'post';

    /**
     * @var array
     */
    public $data = array();

    /**
     * GlobalRules constructor.
     */
    public function __construct()
    {
        $this->wpacuFor      = Misc::getVar('request', 'wpacu_for', $this->wpacuFor);
        $this->wpacuPostType = Misc::getVar('request', 'wpacu_post_type', $this->wpacuPostType);

        if (isset($_REQUEST['wpacu_update']) && $_REQUEST['wpacu_update'] == 1) {
            $this->update();
        }
    }

    /**
     * @return array
     */
    public function getCount()
    {
        $values = array();

        if ($this->wpacuFor === 'everywhere') {
            $values = Main::instance()->getGlobalUnload();
        } elseif ($this->wpacuFor === 'post_types') {
            $values = Main::instance()->getBulkUnload('post_type', $this->wpacuPostType);
        }

        return $values;
    }

    /**
     *
     */
    public function pageBulkUnloads()
    {
        $this->data['for'] = $this->wpacuFor;

        if ($this->wpacuFor === 'post_types') {
            $this->data['post_type'] = $this->wpacuPostType;

            // Get All Post Types
            $postTypes = get_post_types(array('public' => true));
            $this->data['post_types_list'] = $this->filterPostTypesList($postTypes);
        }

        $this->data['values'] = $this->getCount();

        $this->data['nonce_name'] = Update::NONCE_FIELD_NAME;
        $this->data['nonce_action'] = Update::NONCE_ACTION_NAME;

        Main::instance()->parseTemplate('admin-page-settings-bulk-unloads', $this->data, true);
    }

    /**
     * @param $postTypes
     *
     * @return mixed
     */
    public function filterPostTypesList($postTypes)
    {
        foreach ($postTypes as $postTypeKey => $postTypeValue) {
            if ($postTypeKey === 'product' && Misc::isWooCommerceActive()) {
                $postTypes[$postTypeKey] = 'product &#10230; WooCommerce';
            }
        }

        return $postTypes;
    }

    /**
     *
     */
    public function update()
    {
	    check_admin_referer('wpacu_bulk_unloads_update');

        $wpacuUpdate = new Update;

        if ($this->wpacuFor === 'everywhere') {
            $removed = $wpacuUpdate->removeEverywhereUnloads(array(), array(), 'post');

            if ($removed) {
                add_action('wpacu_admin_notices', array($this, 'noticeGlobalsRemoved'));
            }
        }

        if ($this->wpacuFor === 'post_types') {
            $removed = $wpacuUpdate->removeBulkUnloads($this->wpacuPostType);

            if ($removed) {
                add_action('wpacu_admin_notices', array($this, 'noticePostTypesRemoved'));
            }
        }
    }

    /**
     *
     */
    public function noticeGlobalsRemoved()
    {
    ?>
        <div class="updated notice wpacu-notice is-dismissible">
            <p><span class="dashicons dashicons-yes"></span> The selected styles/scripts were removed from the global unload list and they will now load in the pages/posts,
                unless you have other rules that would prevent them from loading.</p>
        </div>
    <?php
    }

    /**
     *
     */
    public function noticePostTypesRemoved()
    {
        ?>
        <div class="updated notice wpacu-notice is-dismissible">
            <p><span class="dashicons dashicons-yes"></span> The selected styles/scripts were removed from the unload list for <strong><u><?php echo $this->wpacuPostType; ?></u></strong>
                post type and they will now load in the pages/posts, unless you have other rules that would prevent them from loading.</p>
        </div>
        <?php
    }
}
