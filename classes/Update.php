<?php
namespace WpAssetCleanUp;

/**
 * Class Update
 * @package WpAssetCleanUp
 */
class Update
{
    /**
     *
     */
    const NONCE_ACTION_NAME = 'wpacu_data_update';
    /**
     *
     */
    const NONCE_FIELD_NAME = 'wpacu_data_nonce';

    /**
     *
     */
    public function init()
    {
        if (Main::instance()->frontendShow) {
            add_action('wp', array($this, 'frontendUpdate'), 1);
        }

        // After post/page is saved - update your styles/scripts lists
        add_action('save_post', array($this, 'savePost'));
    }

    /**
     *
     */
    public function frontendUpdate()
    {
        global $post;

        // Check nonce
        $nonceName = self::NONCE_FIELD_NAME;
        $nonceAction = self::NONCE_ACTION_NAME;

        $updateAction = isset($_POST['wpacu_update_asset_frontend']) ?
            $_POST['wpacu_update_asset_frontend'] : '';

        if (! isset($_POST[$nonceName]) || $updateAction != 1 || ! Main::instance()->frontendShow) {
            return;
        }

        // only for admins
        if (! current_user_can('manage_options')) {
            return;
        }

        if (! wp_verify_nonce($_POST[$nonceName], $nonceAction)) {
            $postUrlAnchor = get_permalink($post->ID).'#wpacu_wrap_assets';
            wp_die(
                sprintf(
                    __('The nonce expired or is not correct, thus the request was not processed. %sPlease retry%s.', WPACU_PLUGIN_NAME),
                    '<a href="'.$postUrlAnchor.'">',
                    '</a>'
                ),
                __('Nonce Expired', WPACU_PLUGIN_NAME)
            );
        }

        if (Misc::isHomePage()) {
            $wpacuNoLoadAssets = isset($_POST[WPACU_PLUGIN_NAME])
                ? $_POST[WPACU_PLUGIN_NAME] : array();

            $this->updateFrontPage($wpacuNoLoadAssets);
        } else {
            $this->savePost($post->ID, $post);
        }
    }

    /**
     * Save post metadata when a post is saved.
     *
     * @param $postId
     * @param array $post
     */
    public function savePost($postId, $post = array())
    {
        if (empty($post)) {
            global $post;
        }

        if (! isset($post->ID)) {
            return;
        }

        // Has to be a public post type
        $obj = get_post_type_object($post->post_type);

        if ($obj->public < 1) {
            return;
        }

        // only for admins
        if (! current_user_can('manage_options')) {
            return;
        }

        $wpacuNoLoadAssets = isset($_POST[WPACU_PLUGIN_NAME])
            ? $_POST[WPACU_PLUGIN_NAME] : array();

        if (is_array($wpacuNoLoadAssets)) {
            global $wpdb;

            $noUpdate = false;

            // Is the list empty?
            if (empty($wpacuNoLoadAssets)) {
                // Remove any row with no results
                $wpdb->delete(
                    $wpdb->postmeta,
                    array('post_id' => $postId, 'meta_key' => '_' . WPACU_PLUGIN_NAME . '_no_load')
                );
                $noUpdate = true;
            }

            if (! $noUpdate) {
                $jsonNoAssetsLoadList = json_encode($wpacuNoLoadAssets);

                if (! add_post_meta($postId, '_' . WPACU_PLUGIN_NAME . '_no_load', $jsonNoAssetsLoadList, true)) {
                    update_post_meta($postId, '_' . WPACU_PLUGIN_NAME . '_no_load', $jsonNoAssetsLoadList);
                }
            }
        }

        // If globally disabled, make exception to load for submitted assets
        $this->saveLoadExceptions('post', $postId);

        // Any global (all pages / everywhere) unloads or removed?
        $this->saveToEverywhereUnloads();
        $this->removeEverywhereUnloads();

        // Any bulk unloads or removed? (e.g. all pages of a certain post type)
        $this->saveToBulkUnloads();
        $this->removeBulkUnloads();
    }

    /**
     * @param $wpacuNoLoadAssets
     */
    public function updateFrontPage($wpacuNoLoadAssets)
    {
        if (! is_array($wpacuNoLoadAssets)) {
            return; // only arrays (empty or not) should be used
        }

        $jsonNoAssetsLoadList = json_encode($wpacuNoLoadAssets);

        if (! update_option(WPACU_PLUGIN_NAME . '_front_page_no_load', $jsonNoAssetsLoadList)) {
            add_option(WPACU_PLUGIN_NAME . '_front_page_no_load', $jsonNoAssetsLoadList);
        }

        // If globally disabled, make exception to load for submitted assets
        $this->saveLoadExceptions('front_page');

        // Any global unloads or removed?
        $this->saveToEverywhereUnloads();
        $this->removeEverywhereUnloads();
    }

    /**
     * @param string $type
     * @param string $postId
     * @return bool|void
     */
    public function saveLoadExceptions($type = 'post', $postId = '')
    {
        if ($type == 'post' && !$postId) {
            // $postId needs to have a value if $type is a 'post' type
            return false;
        }

        if (! in_array($type, array('post', 'front_page'))) {
            // Invalid request
            return false;
        }

        // Any global upload options
        $isPostOptionStyles = (isset($_POST['wpacu_styles_load_it']) && ! empty($_POST['wpacu_styles_load_it']));
        $isPostOptionScripts = (isset($_POST['wpacu_scripts_load_it']) && ! empty($_POST['wpacu_scripts_load_it']));

        $loadExceptionsStyles = $loadExceptionsScripts = array();

        // Clear existing list first
        if ($type == 'post') {
            delete_post_meta($postId, '_' . WPACU_PLUGIN_NAME . '_load_exceptions');
        } elseif ($type == 'front_page') {
            delete_option(WPACU_PLUGIN_NAME . '_front_page_load_exceptions');
        }

        if (! $isPostOptionStyles && ! $isPostOptionScripts) {
            return false;
        }

        // Load Exception
        if (isset($_POST['wpacu_styles_load_it']) && ! empty($_POST['wpacu_styles_load_it'])) {
            foreach ($_POST['wpacu_styles_load_it'] as $wpacuHandle) {
                // Do not append it if the global unload is removed
                if (isset($_POST['wpacu_options_styles'][$wpacuHandle])
                    && $_POST['wpacu_options_styles'][$wpacuHandle] == 'remove') {
                    continue;
                }
                $loadExceptionsStyles[] = $wpacuHandle;
            }
        }

        if (! empty($_POST['wpacu_scripts_load_it'])) {
            foreach ($_POST['wpacu_scripts_load_it'] as $wpacuHandle) {
                // Do not append it if the global unload is removed
                if (isset($_POST['wpacu_options_scripts'][$wpacuHandle])
                    && $_POST['wpacu_options_scripts'][$wpacuHandle] == 'remove') {
                    continue;
                }
                $loadExceptionsScripts[] = $wpacuHandle;
            }
        }

        if (! empty($loadExceptionsStyles) || ! empty($loadExceptionsScripts)) {
            // Default
            $list =  array('styles' => array(), 'scripts' => array());

            // Build list
            if (! empty($loadExceptionsStyles)) {
                foreach ($loadExceptionsStyles as $postHandle) {
                    $list['styles'][] = $postHandle;
                }
            }

            if (! empty($loadExceptionsScripts)) {
                foreach ($loadExceptionsScripts as $postHandle) {
                    $list['scripts'][] = $postHandle;
                }
            }

            if (is_array($list['styles'])) {
                $list['styles'] = array_unique($list['styles']);
            }

            if (is_array($list['scripts'])) {
                $list['scripts'] = array_unique($list['scripts']);
            }

            $jsonLoadExceptions = json_encode($list);

            if ($type == 'post') {
                if (! add_post_meta($postId, '_' . WPACU_PLUGIN_NAME . '_load_exceptions', $jsonLoadExceptions, true)) {
                    update_post_meta($postId, '_' . WPACU_PLUGIN_NAME . '_load_exceptions', $jsonLoadExceptions);
                }
            } elseif ($type == 'front_page') {
                update_option(WPACU_PLUGIN_NAME . '_front_page_load_exceptions', $jsonLoadExceptions);
            }
        }
    }

    /**
     *
     */
    public function saveToEverywhereUnloads()
    {
        $postStyles = (isset($_POST['wpacu_global_unload_styles']) && is_array($_POST['wpacu_global_unload_styles']))
            ? $_POST['wpacu_global_unload_styles'] : array();

        $postScripts = (isset($_POST['wpacu_global_unload_scripts']) && is_array($_POST['wpacu_global_unload_scripts']))
            ? $_POST['wpacu_global_unload_scripts'] : array();

        // Is there any entry already in JSON format?
        $existingListJson = get_option(WPACU_PLUGIN_NAME.'_global_unload');

        // Default list as array
        $existingListEmpty = array('styles' => array(), 'scripts' => array());

        if (! $existingListJson) {
            $existingList = $existingListEmpty;
        } else {
            $existingList = json_decode($existingListJson, true);

            if (json_last_error() != JSON_ERROR_NONE) {
                $existingList = $existingListEmpty;
            }
        }

        // Append to the list anything from the POST (if any)
        if (! empty($postStyles)) {
            foreach ($postStyles as $postStyleHandle) {
                $existingList['styles'][] = $postStyleHandle;
            }
        }

        if (! empty($postScripts)) {
            foreach ($postScripts as $postScriptHandle) {
                $existingList['scripts'][] = $postScriptHandle;
            }
        }

        // Make sure all entries are unique (no handle duplicates)
        $existingList['styles'] = array_unique($existingList['styles']);
        $existingList['scripts'] = array_unique($existingList['scripts']);

        update_option(
            WPACU_PLUGIN_NAME.'_global_unload',
            json_encode($existingList)
        );
    }

    /**
     * @return bool
     */
    public function removeEverywhereUnloads()
    {
        $stylesList = isset($_POST['wpacu_options_styles']) ? $_POST['wpacu_options_styles'] : array();
        $scriptsList = isset($_POST['wpacu_options_scripts']) ? $_POST['wpacu_options_scripts'] : array();

        $removeStylesList = $removeScriptsList = array();

        $isUpdated = false;

        if (! empty($stylesList)) {
            foreach ($stylesList as $handle => $value) {
                if ($value == 'remove') {
                    $removeStylesList[] = $handle;
                }
            }
        }

        if (! empty($scriptsList)) {
            foreach ($scriptsList as $handle => $value) {
                if ($value == 'remove') {
                    $removeScriptsList[] = $handle;
                }
            }
        }

        $existingListJson = get_option(WPACU_PLUGIN_NAME.'_global_unload');

        if (! $existingListJson) {
            return false;
        }

        $existingList = json_decode($existingListJson, true);

        if (json_last_error() == JSON_ERROR_NONE) {
            foreach (array('styles', 'scripts') as $assetType) {
                if ($assetType === 'styles') {
                    $list = $removeStylesList;
                } elseif ($assetType === 'scripts') {
                    $list = $removeScriptsList;
                }

                if (empty($list)) {
                    continue;
                }

                foreach ($list as $handle) {
                    $handleKey = array_search($handle, $existingList[$assetType]);

                    if ($handleKey !== false) {
                        unset($existingList[$assetType][$handleKey]);
                        $isUpdated = true;
                    }
                }
            }

            if ($isUpdated) {
                update_option(
                    WPACU_PLUGIN_NAME . '_global_unload',
                    json_encode($existingList)
                );
            }
        }

        return $isUpdated;
    }

    /**
     *
     */
    public function saveToBulkUnloads()
    {
        global $post;

        $postType = $post->post_type;

        $postStyles = (isset($_POST['wpacu_bulk_unload_styles']) && is_array($_POST['wpacu_bulk_unload_styles']))
            ? $_POST['wpacu_bulk_unload_styles'] : array();

        $postScripts = (isset($_POST['wpacu_bulk_unload_scripts']) && is_array($_POST['wpacu_bulk_unload_scripts']))
            ? $_POST['wpacu_bulk_unload_scripts'] : array();

        // Is there any entry already in JSON format?
        $existingListJson = get_option(WPACU_PLUGIN_NAME.'_bulk_unload');

        // Default list as array
        $existingListEmpty = array(
            'styles'  => array('post_type' => array($postType => array())),
            'scripts' => array('post_type' => array($postType => array()))
        );

        if (! $existingListJson) {
            $existingList = $existingListEmpty;
        } else {
            $existingList = json_decode($existingListJson, true);

            if (json_last_error() != JSON_ERROR_NONE) {
                $existingList = $existingListEmpty;
            }
        }

        // Append to the list anything from the POST (if any)
        // Make sure all entries are unique (no handle duplicates)
        $list = array();

        foreach (array('styles', 'scripts') as $assetType) {
            if ($assetType === 'styles') {
                $list = $postStyles;
            } elseif ($assetType === 'scripts') {
                $list = $postScripts;
            }

            if (empty($list)) {
                continue;
            }

            foreach ($list as $bulkType => $values) {
                if (empty($values)) {
                    continue;
                }

                if ($bulkType === 'post_type') {
                    foreach ($values as $postType => $handles) {
                        $existingList[$assetType]['post_type'][$postType] = array_unique($handles);
                    }
                }
            }
        }

        update_option(
            WPACU_PLUGIN_NAME.'_bulk_unload',
            json_encode($existingList)
        );
    }

    /**
     * @param string $postType
     * @return bool
     */
    public function removeBulkUnloads($postType = '')
    {
        if (! $postType) {
            global $post;

            // At this time (12 Nov 2016) post type unload is the only option in bulk unloads
            $postType = $post->post_type;
        }

        $stylesList = isset($_POST['wpacu_options_post_type_styles'])
            ? $_POST['wpacu_options_post_type_styles'] : array();

        $scriptsList = isset($_POST['wpacu_options_post_type_scripts'])
            ? $_POST['wpacu_options_post_type_scripts'] : array();

        $removeStylesList = $removeScriptsList = array();

        $isUpdated = false;

        if (! empty($stylesList)) {
            foreach ($stylesList as $handle => $value) {
                if ($value == 'remove') {
                    $removeStylesList[] = $handle;
                }
            }
        }

        if (! empty($scriptsList)) {
            foreach ($scriptsList as $handle => $value) {
                if ($value == 'remove') {
                    $removeScriptsList[] = $handle;
                }
            }
        }

        $existingListJson = get_option(WPACU_PLUGIN_NAME.'_bulk_unload');

        if (! $existingListJson) {
            return false;
        }

        $existingList = json_decode($existingListJson, true);

        if (json_last_error() == JSON_ERROR_NONE) {
            $list = array();

            foreach (array('styles', 'scripts') as $assetType) {
                if ($assetType === 'styles') {
                    $list = $removeStylesList;
                } elseif ($assetType === 'scripts') {
                    $list = $removeScriptsList;
                }

                if (empty($list)) {
                    continue;
                }

                foreach ($existingList[$assetType]['post_type'][$postType] as $handleKey => $handle) {
                    if (in_array($handle, $list)) {
                        unset($existingList[$assetType]['post_type'][$postType][$handleKey]);
                        $isUpdated = true;
                    }
                }
            }

            update_option(
                WPACU_PLUGIN_NAME.'_bulk_unload',
                json_encode($existingList)
            );
        }

        return $isUpdated;
    }
}
