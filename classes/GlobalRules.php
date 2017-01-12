<?php
namespace WpAssetCleanUp;

/**
 * Class GlobalRules
 * @package WpAssetCleanUp
 */
class GlobalRules
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
        $this->wpacuFor = isset($_REQUEST['wpacu_for'])
            ? $_REQUEST['wpacu_for']
            : $this->wpacuFor;

        $this->wpacuPostType = isset($_REQUEST['wpacu_post_type'])
            ? $_REQUEST['wpacu_post_type']
            : $this->wpacuPostType;

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
            $values = Main::instance()->getPostTypeUnload($this->wpacuPostType);
        }

        return $values;
    }

    /**
     *
     */
    public function page()
    {
        $this->data['for'] = $this->wpacuFor;

        if ($this->wpacuFor === 'post_types') {
            $this->data['post_type'] = $this->wpacuPostType;

            // Get All Post Types
            $postTypes = get_post_types(array('public' => true));
            $this->data['post_types_list'] = $postTypes;
        }

        $this->data['values'] = $this->getCount();

        $this->data['nonce_name'] = Update::NONCE_FIELD_NAME;
        $this->data['nonce_action'] = Update::NONCE_ACTION_NAME;

        Main::instance()->parseTemplate('settings-globals', $this->data, true);
    }

    /**
     *
     */
    public function update()
    {
        $wpacuUpdate = new Update;

        if ($this->wpacuFor === 'everywhere') {
            $removed = $wpacuUpdate->removeEverywhereUnloads();

            if ($removed) {
                add_action('admin_notices', array($this, 'noticeGlobalsRemoved'));
            }
        }

        if ($this->wpacuFor === 'post_types') {
            $removed = $wpacuUpdate->removeBulkUnloads($this->wpacuPostType);

            if ($removed) {
                add_action('admin_notices', array($this, 'noticePostTypesRemoved'));
            }
        }
    }

    /**
     *
     */
    public function noticeGlobalsRemoved()
    {
    ?>
        <div class="updated notice is-dismissible">
            <p>The selected styles/scripts were removed from the global unload list and they will now load in the pages/posts,
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
        <div class="updated notice is-dismissible">
            <p>The selected styles/scripts were removed from the unload list for <strong><u><?php echo $this->wpacuPostType; ?></u></strong>
                post type and they will now load in the pages/posts, unless you have other rules that would prevent them from loading.</p>
        </div>
        <?php
    }
}
