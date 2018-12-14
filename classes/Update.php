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
	 * @var bool
	 */
	public $frontEndUpdateTriggered = false;

	/**
	 * @var array
	 */
	public $frontEndUpdateFor = array(
		'homepage' => false,
		'page'     => false
	);

	/**
	 * @var array
	 */
	public $updateDoneMsg = array();

	/**
	 * Update constructor.
	 */
	public function __construct()
	{
		$this->updateDoneMsg['homepage'] = <<<HTML
<span class="dashicons dashicons-yes"></span> The homepage's settings were updated. Please make sure the homepage's cache is cleared (if you're using a caching plugin or a server-side caching solution) to immediately have the changes applied for every visitor.
HTML;

		$this->updateDoneMsg['page'] = <<<HTML
<span class="dashicons dashicons-yes"></span> This page's settings were updated. Please make sure the page's cache is cleared (if you're using a caching plugin or a server-side caching solution) to immediately have the changes applied for every visitor.
HTML;
	}

    /**
     *
     */
    public function init()
    {
	    // Triggers on front-end view
	    add_action('plugins_loaded', array($this, 'initAfterPluginsLoaded'), 11);

        // After post/page is saved - update your styles/scripts lists
        // This triggers ONLY in the Dashboard after "Update" button is clicked (on Edit mode)
        add_action('save_post', array($this, 'savePost'));
    }

	/**
	 *
	 */
	public function initAfterPluginsLoaded()
	{
		if (! is_admin() && Main::instance()->settings['frontend_show']) {
			add_action( 'wp', array( $this, 'frontendUpdate' ), 9 );
			add_action( 'template_redirect', array( $this, 'redirectAfterFrontEndUpdate' ) );
		}
	}

    /**
     * TODO: Remove priority later on as it's not needed anymore because there is a redirect after form submit
     * Priority: 9 (AFTER current post ID is correctly retrieved and BEFORE the data from the database is fetched)
     * Form was submitted in the frontend view (not Dashboard) from a singular page, front-page etc.
     */
    public function frontendUpdate()
    {
	    @session_start();

	    if (isset($_SESSION['wpacu_page_just_updated'])) {
		    define('WPACU_PAGE_JUST_UPDATED', true);
		    unset($_SESSION['wpacu_page_just_updated']);
	    }

        $postId = 0;

        if (Main::instance()->currentPostId > 0) {
            $postId = Main::instance()->currentPostId;
        }

        // Check nonce
        $nonceName = self::NONCE_FIELD_NAME;
        $nonceAction = self::NONCE_ACTION_NAME;

        $updateAction = Misc::getVar('post', 'wpacu_update_asset_frontend');

        if (! isset($_POST[$nonceName]) || $updateAction != 1 || ! Main::instance()->settings['frontend_show']) {
            return;
        }

        // only for admins
        if (! Menu::userCanManageAssets()) {
            return;
        }

        if (! wp_verify_nonce($_POST[$nonceName], $nonceAction)) {
            $postUrlAnchor = $_SERVER['REQUEST_URI'].'#wpacu_wrap_assets';
            wp_die(
                sprintf(
                    __('The nonce expired or is not correct, thus the request was not processed. %sPlease retry%s.', WPACU_PLUGIN_TEXT_DOMAIN),
                    '<a href="'.$postUrlAnchor.'">',
                    '</a>'
                ),
                __('Nonce Expired', WPACU_PLUGIN_TEXT_DOMAIN)
            );
        }

        $this->frontEndUpdateTriggered = true;

        // Form submitted from the homepage
	    // e.g. from a page such as latest blog posts, not a static page that was selected as home page)
        if (Misc::isHomePage() && ! ($postId > 0)) {
            $wpacuNoLoadAssets = isset($_POST[WPACU_PLUGIN_ID])
                ? $_POST[WPACU_PLUGIN_ID] : array();

            $this->updateFrontPage($wpacuNoLoadAssets);
            return;
        }

	    // Form submitted from a Singular Page
	    // e.g. post, page, custom post type such as 'product' page from WooCommerce, home page (static page selected as front page)
        if ($postId > 0) {
            $post = get_post($postId);
            $this->savePost($post->ID, $post);
            return;
        }

	    // [wpacu_pro]
        // Form Submitted from a page such as taxonomy (e.g. 'category'), author page, 404 page, search page etc.
        do_action('wpacu_pro_frontend_update');
	    // [/wpacu_pro]
    }

	/**
	 *
	 */
	public function redirectAfterFrontEndUpdate()
    {
    	// It triggers ONLY on front-end view, when a valid POST request is made
    	if ((! $this->frontEndUpdateTriggered) || is_admin() || (! (isset($_POST['wpacu_unload_assets_area_loaded']) && $_POST['wpacu_unload_assets_area_loaded']))) {
    		return;
	    }

	    $parseUrl = parse_url($_SERVER['REQUEST_URI']);

	    $location = $parseUrl['path'];

	    $paramsToAdd = array(
	    	'wpacu_time' => time(),
		    'nocache'    => 'true'
	    );

	    $extraParamsSign = '?';

	    if (isset($parseUrl['query']) && $parseUrl['query']) {
		    parse_str($parseUrl['query'], $existingQueryParams);

		    foreach (array_keys($paramsToAdd) as $paramKey) {
			    if ( isset( $existingQueryParams[$paramKey] ) ) {
				    unset( $existingQueryParams[$paramKey] );
			    }
		    }

		    if (! empty($existingQueryParams)) {
			    $location .= '?'.http_build_query($existingQueryParams);
			    $extraParamsSign = '&';
		    }
	    }

	    $location .= $extraParamsSign . http_build_query($paramsToAdd) . '#wpacu_wrap_assets';

	    $_SESSION['wpacu_page_just_updated'] = true;

	    wp_safe_redirect($location);
    	exit();
    }

    /**
     * Save post metadata when a post is saved (not for the "Latest Blog Posts" home page type)
     * Only for post types
     *
     * Admin: triggered via hook
     * Front-end view: triggered by direct call
     *
     * @param $postId
     * @param array $post
     */
    public function savePost($postId, $post = array())
    {
    	// This is triggered only if the "Asset CleanUp" meta box was loaded with the list of assets
	    // Otherwise, $_POST[WPACU_PLUGIN_ID] will be taken as empty which might be not if there are values in the database
    	if (! (isset($_POST['wpacu_unload_assets_area_loaded']) && $_POST['wpacu_unload_assets_area_loaded'])) {
    	    return;
	    }

        if (empty($post)) {
            global $post;
        }

        if (! isset($post->ID) || ! isset($post->post_type)) {
            return;
        }

        // Has to be a public post type
        $obj = get_post_type_object($post->post_type);

        if ($obj->public < 1) {
            return;
        }

        // only for admins
        if (! Menu::userCanManageAssets()) {
            return;
        }

        $wpacuNoLoadAssets = isset($_POST[WPACU_PLUGIN_ID])
            ? $_POST[WPACU_PLUGIN_ID] : array();

        if (is_array($wpacuNoLoadAssets)) {
            global $wpdb;

            $noUpdate = false;

            // Is the list empty?
            if (empty($wpacuNoLoadAssets)) {
                // Remove any row with no results
                $wpdb->delete(
                    $wpdb->postmeta,
                    array('post_id' => $postId, 'meta_key' => '_' . WPACU_PLUGIN_ID . '_no_load')
                );
                $noUpdate = true;
            }

            if (! $noUpdate) {
                $jsonNoAssetsLoadList = json_encode($wpacuNoLoadAssets);

                if (! add_post_meta($postId, '_' . WPACU_PLUGIN_ID . '_no_load', $jsonNoAssetsLoadList, true)) {
                    update_post_meta($postId, '_' . WPACU_PLUGIN_ID . '_no_load', $jsonNoAssetsLoadList);
                }
            }
        }

        // If globally disabled, make exception to load for submitted assets
        $this->saveLoadExceptions('post', $postId);

	    // Add / Remove Site-wide Unloads
	    $this->updateEverywhereUnloads();

        // Any bulk unloads or removed? (e.g. all pages of a certain post type)
        $this->saveToBulkUnloads();
        $this->removeBulkUnloads();
    }

    /**
     * @param $wpacuNoLoadAssets
     */
    public function updateFrontPage($wpacuNoLoadAssets)
    {
    	// Needed in case the user clicks "Update" on a page without assets retrieved
	    // Avoid resetting the existing values
	    if (! (isset($_POST['wpacu_unload_assets_area_loaded']) && $_POST['wpacu_unload_assets_area_loaded'])) {
		    return;
	    }

        if (! is_array($wpacuNoLoadAssets)) {
            return; // only arrays (empty or not) should be used
        }

        $jsonNoAssetsLoadList = json_encode($wpacuNoLoadAssets);

        if (! update_option( WPACU_PLUGIN_ID . '_front_page_no_load', $jsonNoAssetsLoadList)) {
            add_option( WPACU_PLUGIN_ID . '_front_page_no_load', $jsonNoAssetsLoadList);
        }

        // If globally disabled, make exception to load for submitted assets
        $this->saveLoadExceptions('front_page');

        // Add / Remove Site-wide Unloads
		$this->updateEverywhereUnloads();

	    add_action('wpacu_admin_notices', array($this, 'homePageUpdated'));

	    $this->frontEndUpdateFor['homepage'] = true;
    }

	/**
	/**
	 *
	 */
	public function homePageUpdated()
	{
		?>
        <div class="updated notice wpacu-notice is-dismissible">
            <p><?php echo $this->updateDoneMsg['homepage']; ?></p>
        </div>
		<?php
	}

	/**
	 * Lite: For Singular Page (Post, Page, Custom Post Type) and Front Page (Home Page)
	 * Pro: 'for_pro' would trigger the actions from the premium extension (if available)
	 *
	 * This is the function that clears and updates the load exceptions for any of the requested pages
	 *
	 * This method SHOULD NOT be triggered within an AJAX call
	 *
	 * @param string $type
	 * @param string $postId
	 */
	public function saveLoadExceptions($type = 'post', $postId = '')
    {
        if ($type === 'post' && !$postId) {
            // $postId needs to have a value if $type is a 'post' type
            return;
        }

        // Any load exceptions?
        $isPostOptionStyles = (isset($_POST['wpacu_styles_load_it']) && ! empty($_POST['wpacu_styles_load_it']));
        $isPostOptionScripts = (isset($_POST['wpacu_scripts_load_it']) && ! empty($_POST['wpacu_scripts_load_it']));

        $loadExceptionsStyles = $loadExceptionsScripts = array();

        // Clear existing list first
        if ($type === 'post') {
            delete_post_meta($postId, '_' . WPACU_PLUGIN_ID . '_load_exceptions');
        } elseif ($type === 'front_page') {
            delete_option( WPACU_PLUGIN_ID . '_front_page_load_exceptions');
        } /* [wpacu_pro] */ elseif ($type === 'for_pro') {
	        // Clear existing list for pages like: taxonomy, 404, search, date etc.
	        do_action( 'wpacu_pro_clear_load_exceptions' );
        } /* [/wpacu_pro] */

        if (! $isPostOptionStyles && ! $isPostOptionScripts) {
            return;
        }

        // Load Exception
        if (isset($_POST['wpacu_styles_load_it']) && ! empty($_POST['wpacu_styles_load_it'])) {
            foreach ($_POST['wpacu_styles_load_it'] as $wpacuHandle) {
                // Do not append it if the global unload is removed
                if (isset($_POST['wpacu_options_styles'][$wpacuHandle])
                    && $_POST['wpacu_options_styles'][$wpacuHandle] === 'remove') {
                    continue;
                }
                $loadExceptionsStyles[] = $wpacuHandle;
            }
        }

        if (! empty($_POST['wpacu_scripts_load_it'])) {
            foreach ($_POST['wpacu_scripts_load_it'] as $wpacuHandle) {
                // Do not append it if the global unload is removed
                if (isset($_POST['wpacu_options_scripts'][$wpacuHandle])
                    && $_POST['wpacu_options_scripts'][$wpacuHandle] === 'remove') {
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

            if ($type === 'post') {
                if (! add_post_meta($postId, '_' . WPACU_PLUGIN_ID . '_load_exceptions', $jsonLoadExceptions, true)) {
                    update_post_meta($postId, '_' . WPACU_PLUGIN_ID . '_load_exceptions', $jsonLoadExceptions);
                }
            } elseif ($type === 'front_page') {
                update_option( WPACU_PLUGIN_ID . '_front_page_load_exceptions', $jsonLoadExceptions);
            } /* [wpacu_pro] */ elseif ($type === 'for_pro') {
	            // Update any load extensions for pages like: taxonomy, 404, search, date etc.
	            do_action( 'wpacu_pro_update_load_exceptions', $jsonLoadExceptions );
            } /* [/wpacu_pro] */
        }
    }

	/**
	 * Triggers either "saveToEverywhereUnloads" or "removeEverywhereUnloads" methods
	 */
	public function updateEverywhereUnloads()
    {
	    /*
	     * Any global (all pages / everywhere) UNLOADS?
	     * Coming from a POST request
	     */
	    $reqStyles = (isset($_POST['wpacu_global_unload_styles']) && is_array($_POST['wpacu_global_unload_styles']))
		    ? $_POST['wpacu_global_unload_styles'] : array();

	    $reqScripts = (isset($_POST['wpacu_global_unload_scripts']) && is_array($_POST['wpacu_global_unload_scripts']))
		    ? $_POST['wpacu_global_unload_scripts'] : array();

	    $this->saveToEverywhereUnloads($reqStyles, $reqScripts);

	    /*
	     * Any global (all pages / everywhere) REMOVED?
	     * Coming from a POST request
	     */
	    $this->removeEverywhereUnloads(array(), array(), 'post');
    }

	/**
	 * @param array $reqStyles
	 * @param array $reqScripts
	 */
	public function saveToEverywhereUnloads($reqStyles = array(), $reqScripts = array())
    {
        // Is there any entry already in JSON format?
        $existingListJson = get_option( WPACU_PLUGIN_ID . '_global_unload');

        // Default list as array
        $existingListEmpty = array('styles' => array(), 'scripts' => array());

	    $existingListData = Main::instance()->existingList($existingListJson, $existingListEmpty);
	    $existingList = $existingListData['list'];

        // Append to the list anything from the POST (if any)
        if (! empty($reqStyles)) {
            foreach ($reqStyles as $reqStyleHandle) {
                $existingList['styles'][] = $reqStyleHandle;
            }
        }

        if (! empty($reqScripts)) {
            foreach ($reqScripts as $reqScriptHandle) {
                $existingList['scripts'][] = $reqScriptHandle;
            }
        }

        // Make sure all entries are unique (no handle duplicates)
        $existingList['styles'] = array_unique($existingList['styles']);
        $existingList['scripts'] = array_unique($existingList['scripts']);

        update_option( WPACU_PLUGIN_ID . '_global_unload', json_encode($existingList));
    }

	/**
	 * @param array $stylesList
	 * @param array $scriptsList
	 * @param string $checkType
	 *
	 * @return bool
	 */
	public function removeEverywhereUnloads($stylesList = array(), $scriptsList = array(), $checkType = '')
    {
    	if ($checkType === 'post') {
		    $stylesList  = Misc::getVar('post', 'wpacu_options_styles', array());
		    $scriptsList = Misc::getVar('post', 'wpacu_options_scripts', array());
	    }

        $removeStylesList = $removeScriptsList = array();

        $isUpdated = false;

        if (! empty($stylesList)) {
            foreach ($stylesList as $handle => $action) {
                if ($action === 'remove') {
                    $removeStylesList[] = $handle;
                }
            }
        }

        if (! empty($scriptsList)) {
            foreach ($scriptsList as $handle => $action) {
                if ($action === 'remove') {
                    $removeScriptsList[] = $handle;
                }
            }
        }

        $existingListJson = get_option( WPACU_PLUGIN_ID . '_global_unload');

        if (! $existingListJson) {
            return false;
        }

        $existingList = json_decode($existingListJson, true);

        if (json_last_error() === JSON_ERROR_NONE) {
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
                update_option( WPACU_PLUGIN_ID . '_global_unload', json_encode($existingList));
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

	    $postType = isset( $post->post_type ) ? $post->post_type : false;

	    // Free Version: It only deals with 'post_type' bulk unloads
	    if ( ! $postType ) {
		    return;
	    }

        $postStyles = (isset($_POST['wpacu_bulk_unload_styles']) && is_array($_POST['wpacu_bulk_unload_styles']))
            ? $_POST['wpacu_bulk_unload_styles'] : array();

        $postScripts = (isset($_POST['wpacu_bulk_unload_scripts']) && is_array($_POST['wpacu_bulk_unload_scripts']))
            ? $_POST['wpacu_bulk_unload_scripts'] : array();

        // Is there any entry already in JSON format?
        $existingListJson = get_option( WPACU_PLUGIN_ID . '_bulk_unload');

        // Default list as array
        $existingListEmpty = array(
            'styles'  => array('post_type' => array($postType => array())),
            'scripts' => array('post_type' => array($postType => array()))
        );

	    $existingListData = Main::instance()->existingList($existingListJson, $existingListEmpty);
	    $existingList = $existingListData['list'];

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
                        if (empty($handles)) {
                            continue;
                        }

                    	foreach (array_unique($handles) as $handle) {
		                    $existingList[ $assetType ]['post_type'][ $postType ][] = $handle;
	                    }

	                    $existingList[ $assetType ]['post_type'][ $postType ] = array_unique($existingList[ $assetType ]['post_type'][ $postType ]);
                    }
                }
            }
        }

        update_option( WPACU_PLUGIN_ID . '_bulk_unload', json_encode($existingList));
    }

    /**
     * Lite Version: For post, pages, custom post types
     * @param mixed $postType
     * @return bool
     */
    public function removeBulkUnloads($postType = '')
    {
        if (! $postType) {
            global $post;

            // In the lite version, post type unload is the only option for bulk unloads
            $postType = isset($post->post_type) ? $post->post_type : false;

            if (! $postType) {
            	return false;
            }
        }

	    $bulkType = 'post_type';

	    $stylesList = Misc::getVar('post', 'wpacu_options_' . $bulkType . '_styles', array());
	    $scriptsList = Misc::getVar('post', 'wpacu_options_' . $bulkType . '_scripts', array());

        if (empty($stylesList) && empty($scriptsList)) {
        	return false;
        }

        $removeStylesList = $removeScriptsList = array();

        $isUpdated = false;

        if (! empty($stylesList)) {
            foreach ($stylesList as $handle => $action) {
                if ($action === 'remove') {
                    $removeStylesList[] = $handle;
                }
            }
        }

        if (! empty($scriptsList)) {
            foreach ($scriptsList as $handle => $action) {
                if ($action === 'remove') {
                    $removeScriptsList[] = $handle;
                }
            }
        }

        $existingListJson = get_option( WPACU_PLUGIN_ID . '_bulk_unload');

        if (! $existingListJson) {
            return false;
        }

        $existingList = json_decode($existingListJson, true);

        if (json_last_error() === JSON_ERROR_NONE) {
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

            update_option( WPACU_PLUGIN_ID . '_bulk_unload', json_encode($existingList));
        }

        return $isUpdated;
    }
}
