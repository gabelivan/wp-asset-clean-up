<?php
namespace WpAssetCleanUp;

/**
 * Class Main
 * @package WpAssetCleanUp
 */
class Main
{
    /**
     *
     */
    const START_DEL = 'BEGIN WPACU PLUGIN JSON';

    /**
     *
     */
    const END_DEL = 'END WPACU PLUGIN JSON';

    /**
     * @var string
     * Can be managed in the Dashboard within the plugin's settings
     * e.g. 'direct', 'wp_remote_post'
     */
    public static $domGetType = 'direct';

	/**
	 * @var string
	 */
	public $assetsRemoved = '';

    /**
     * @var array
     */
    public $globalUnloaded = array();

    /**
     * @var array
     */
    public $loadExceptions = array('styles' => array(), 'scripts' => array());

    /**
     * @var
     */
    public $fetchUrl;

    // [wpacu_lite]
    /**
     * @var
     */
    public $isUpdateable = true;
	// [/wpacu_lite]

    /**
     * @var int
     */
    public $currentPostId = 0;

    /**
     * @var array
     */
    public $currentPost = array();

    /**
     * @var array
     */
    public $vars = array('woo_url_not_match' => false, 'is_woo_shop_page' => false);

    /**
     * This is set to `true` only if "Manage in the Front-end?" is enabled in plugin's settings
     * and the logged-in administrator with plugin activation privileges
     * is outside the Dashboard viewing the pages like a visitor
     *
     * @var bool
     */
    public $isFrontendEditView = false;

    /**
     * @var array
     */
    public $assetsInFooter = array();

    /**
     * @var array
     */
    public $wpAllScripts = array();

    /**
     * @var array
     */
    public $wpAllStyles = array();

	/**
	 * @var int
	 */
	public $lastScriptPos = 1;

	/**
	 * @var int
	 */
	public $lastStylePos = 1;

    /**
     * @var array
     */
    public $postTypesUnloaded = array();

	/**
	 * @var array
	 */
	public $settings = array();

	/**
	 * @var bool
	 */
	public $isAjaxCall = false;

    /**
     * @var Main|null
     */
    private static $singleton = null;

    /**
     * @return null|Main
     */
    public static function instance()
    {
        if (self::$singleton === null) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }

    /**
     * Parser constructor.
     */
    public function __construct()
    {
        if (array_key_exists(WPACU_LOAD_ASSETS_REQ_KEY, $_REQUEST)) {
            add_filter('w3tc_minify_enable', '__return_false');
        }

        // Early Triggers
        add_action('wp', array($this, 'setVarsBeforeUpdate'), 8);
        add_action('wp', array($this, 'setVarsAfterAnyUpdate'), 10);

	    $this->isAjaxCall = (! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

	    // "Direct" AJAX call to the page (regular AJAX)
	    // Do not print the admin bar as it's not relevant
	    if ($this->isAjaxCall) {
		    Misc::noAdminBarLoad();
	    }

	    // This is triggered AFTER "saveSettings" from 'Settings' class
	    // In case the settings were just updated, the script will get the latest values
	    add_action('plugins_loaded', array($this, 'initAfterPluginsLoaded'), 10);

        // Front-end View - Unload the assets
        // If there are reasons to prevent the unloading in case 'test mode' is enabled,
	    // then the prevention will trigger within filterStyles() and filterScripts()

	    if (! isset($_REQUEST[WPACU_LOAD_ASSETS_REQ_KEY])) { // AJAX call? Do not trigger the code below
		    // Unload Styles - HEAD
		    add_action( 'wp_print_styles', array( $this, 'filterStyles' ), 100000 );

		    // Unload Scripts - HEAD
		    add_action( 'wp_print_scripts', array( $this, 'filterScripts' ), 100000 );

		    // Unload Scripts & Styles - FOOTER
		    // Needs to be triggered very soon as some old plugins/themes use wp_footer() to enqueue scripts
		    // Sometimes styles are loaded in the BODY section of the page
		    add_action( 'wp_print_footer_scripts', array( $this, 'filterScripts' ), 1 );
		    add_action( 'wp_print_footer_scripts', array( $this, 'filterStyles' ), 1 );
	    }

	    // This is recommended to keep active for Lite users as it helps spread the word about the plugin
	    // To remove the notice, consider upgrading to PRO

	    // If you still want to remove it, consider adding the following code in your wp-config (WordPress root file)
	    // define('WPACU_HIDE_HTML_USAGE_COMMENT', true);
	    if (   ! (defined('WPACU_PRO_HIDE_HTML_USAGE_COMMENT') && WPACU_PRO_HIDE_HTML_USAGE_COMMENT)
	        && ! (defined('WPACU_HIDE_HTML_USAGE_COMMENT') && WPACU_HIDE_HTML_USAGE_COMMENT) ) {
		    $this->wpacuUsageNotice();
	    }

	    $this->wpacuHtmlNoticeForAdmin();
    }

	/**
	 *
	 */
	public function initAfterPluginsLoaded()
	{
		$wpacuSettingsClass = new Settings();
		$this->settings = $wpacuSettingsClass->getAll();

		if ($this->settings['dashboard_show'] && $this->settings['dom_get_type']) {
			self::$domGetType = $this->settings['dom_get_type'];
		}

		// Fetch the page in the background to see what scripts/styles are already loading
		if (isset($_REQUEST[WPACU_LOAD_ASSETS_REQ_KEY]) || $this->settings['frontend_show']) {
			if (isset($_REQUEST[WPACU_LOAD_ASSETS_REQ_KEY])) {
				Misc::noAdminBarLoad();
			}

			add_action('wp_head', array($this, 'saveFooterAssets'), 100000000);
			add_action('wp_footer', array($this, 'printScriptsStyles'), PHP_INT_MAX);
		}

		// Do not load the meta box nor do any AJAX calls
		// if the asset management is not enabled for the Dashboard
		if ( $this->settings['dashboard_show'] == 1 ) {
			// Send an AJAX request to get the list of loaded scripts and styles and print it nicely
			add_action(
				'wp_ajax_' . WPACU_PLUGIN_ID . '_get_loaded_assets',
				array( $this, 'ajaxGetJsonListCallback' )
			);

			add_action( 'add_meta_boxes', array( $this, 'addMetaBox' ) );
		}

		if ($this->settings['disable_emojis'] == 1) {
			add_action('init', array($this, 'doDisableEmojis'));
		}
	}

    /**
     * This has to be triggered after 'plugins_loaded' (e.g. in 'wp')
     *
     * Priority: 8 (earliest)
     */
    public function setVarsBeforeUpdate()
    {
        $this->isFrontendEditView = ( $this->settings['frontend_show'] && Menu::userCanManageAssets()
                                      && !isset($_REQUEST[WPACU_LOAD_ASSETS_REQ_KEY])
                                      && !is_admin());

        // it will update $this->isUpdateable;
        $this->getCurrentPostId();
    }

    /**
     * Priority: 10 (latest)
     */
    public function setVarsAfterAnyUpdate()
    {
        if ( ! isset($_REQUEST[WPACU_LOAD_ASSETS_REQ_KEY]) && ! is_admin()) {
            $this->globalUnloaded = $this->getGlobalUnload();

	        // [wpacu_lite]
            if (! $this->isUpdateable) {
                return;
            }
	        // [/wpacu_lite]

            $getCurrentPost = $this->getCurrentPost();

            if (Misc::isHomePage()) {
            	$type = 'front_page';
            } elseif ( ! empty($getCurrentPost) )  {
            	$type = 'post';
	            $post = $getCurrentPost;
	            $this->postTypesUnloaded = $this->getBulkUnload('post_type', $post->post_type);
            } elseif ($this->wpacuProEnabled()) {
            	$type = 'for_pro';
            	// $this->currentPostId should be 0 in this case
            } else {
            	// The request is done for a page such as is_archive(), is_author(), 404, search
	            // and the premium extension is not available, thus no load exceptions are available
            	return;
            }

            $this->loadExceptions = $this->getLoadExceptions($type, $this->currentPostId);
        }
    }

    /**
     * @param $postType
     */
    public function addMetaBox($postType)
    {
        $obj = get_post_type_object($postType);

        if (isset($obj->public) && $obj->public > 0) {
            add_meta_box(
	            WPACU_PLUGIN_ID . '_asset_list',
                __('Asset CleanUp', WPACU_PLUGIN_TEXT_DOMAIN),
                array($this, 'renderMetaBoxContent'),
                $postType,
                'advanced',
                'high'
            );
        }
    }

    /**
     * This is triggered only in the Edit Mode Dashboard View
     */
    public function renderMetaBoxContent()
    {
        global $post;

	    if ($post->ID === null) {
            return;
        }

        $postId = $post->ID;

        $getAssets = true;

        if (get_post_status($postId) !== 'publish') {
            $getAssets = false;
        }

        if ($getAssets) {
            // Add an nonce field so we can check for it later.
            wp_nonce_field( WPACU_PLUGIN_ID . '_meta_box', WPACU_PLUGIN_ID . '_nonce');
        }

        $data = array();

        $data['get_assets'] = $getAssets;

        $data['fetch_url'] = Misc::getPageUrl($postId);

        $this->parseTemplate('meta-box', $data, true);
    }

    /**
     * See if there is any list with scripts to be removed in JSON format
     * Only the handles (the ID of the scripts) are saved
     */
    public function filterScripts()
    {
        if (is_admin()) {
            return;
        }

	    // [wpacu_lite]
        $nonAssetConfigPage = (! $this->isUpdateable && ! Misc::getShowOnFront());
		// [/wpacu_lite]

        // It looks like the page loaded is neither a post, page or the front-page
        // We'll see if there are assets unloaded globally and unload them
        $globalUnload = $this->globalUnloaded;

        // [wpacu_lite]
	    if (! empty($globalUnload['scripts']) && $nonAssetConfigPage) {
            $list = $globalUnload['scripts'];
        } else { // [/wpacu_lite]
		    // Post, Page or Front-page?
            $toRemove = $this->getAssetsUnloaded();

            $jsonList = @json_decode($toRemove);

            $list = array();

            if (isset($jsonList->scripts)) {
                $list = (array)$jsonList->scripts;
            }

            // Any global unloaded styles? Append them
            if (! empty($globalUnload['scripts'])) {
                foreach ($globalUnload['scripts'] as $handleScript) {
                    $list[] = $handleScript;
                }
            }

            if ($this->isSingularPage()) {
                // Any bulk unloaded styles (e.g. for all pages belonging to a post type)? Append them
                if (empty($this->postTypesUnloaded)) {
                    $post = $this->getCurrentPost();
                    $this->postTypesUnloaded = $this->getBulkUnload('post_type', $post->post_type);
                }

                if (!empty($this->postTypesUnloaded['scripts'])) {
                    foreach ($this->postTypesUnloaded['scripts'] as $handleStyle) {
                        $list[] = $handleStyle;
                    }
                }
            }
        // [wpacu_lite]
	    }
		// [/wpacu_lite]

	    $list = apply_filters('wpacu_filter_scripts', array_unique($list));

        // Let's see if there are load exceptions for this page
        if (! empty($list) && ! empty($this->loadExceptions['scripts'])) {
            foreach ($list as $handleKey => $handle) {
                if (in_array($handle, $this->loadExceptions['scripts'])) {
                    unset($list[$handleKey]);
                }
            }
        }

        global $wp_scripts;

        $allScripts = $wp_scripts;

        if (isset($allScripts->registered) && ! empty($allScripts->registered)) {
            $i = $this->lastScriptPos;

            foreach ($allScripts->registered as $handle => $value) {
	            // This could be triggered several times, check if the script already exists
                if (! isset($this->wpAllScripts['registered'][$handle])) {
	                $this->wpAllScripts['registered'][$handle] = $value;
	                $this->wpAllScripts['registered'][$handle]->wpacu_pos = $i;
	                $this->lastScriptPos = $i;
	                $i++;

	                if (in_array($handle, $allScripts->queue)) {
		                $this->wpAllScripts['queue'][] = $handle;
	                }
                }
            }

	        if (isset($this->wpAllScripts['queue']) && ! empty($this->wpAllScripts['queue'])) {
		        $this->wpAllScripts['queue'] = array_unique( $this->wpAllScripts['queue'] );
	        }
        }

	    // Nothing to unload
	    if (empty($list)) {
		    return;
	    }

	    // e.g. for test mode or AJAX calls (where all assets have to load)
	    if ($this->preventUnloadAssets() === true) {
		    return;
	    }

        foreach ($list as $handle) {
            $handle = trim($handle);

            // Special Action for 'jquery-migrate' handler as its tied to 'jquery'
            if ($handle === 'jquery-migrate' && isset($this->wpAllScripts['registered']['jquery'])) {
	            $jQueryRegScript = $this->wpAllScripts['registered']['jquery'];

	            if (isset($jQueryRegScript->deps)) {
		            $jQueryRegScript->deps = array_diff($jQueryRegScript->deps, array('jquery-migrate'));
	            }

				continue;
            }

            wp_deregister_script($handle);
            wp_dequeue_script($handle);
        }
    }

    /**
     * See if there is any list with styles to be removed in JSON format
     * Only the handles (the ID of the styles) is stored
     */
    public function filterStyles()
    {
        if (is_admin()) {
            return;
        }

	    // [wpacu_lite]
        $nonAssetConfigPage = (! $this->isUpdateable && ! Misc::getShowOnFront());
		// [/wpacu_lite]

        // It looks like the page loaded is neither a post, page or the front-page
        // We'll see if there are assets unloaded globally and unload them
        $globalUnload = $this->globalUnloaded;

	    // [wpacu_lite]
        if (! empty($globalUnload['styles']) && $nonAssetConfigPage) {
            $list = $globalUnload['styles'];
        } else { // [/wpacu_lite]
            // Post, Page, Front-page and more (if the Premium Extension is activated)
            $toRemove = $this->getAssetsUnloaded();

            $jsonList = @json_decode($toRemove);

            $list = array();

            if (isset($jsonList->styles)) {
                $list = (array)$jsonList->styles;
            }

            // Any global unloaded styles? Append them
            if (! empty($globalUnload['styles'])) {
                foreach ($globalUnload['styles'] as $handleStyle) {
                    $list[] = $handleStyle;
                }
            }

            if ($this->isSingularPage()) {
                // Any bulk unloaded styles (e.g. for all pages belonging to a post type)? Append them
                if (empty($this->postTypesUnloaded)) {
                    $post = $this->getCurrentPost();
                    $this->postTypesUnloaded = $this->getBulkUnload('post_type', $post->post_type);
                }

                if (!empty($this->postTypesUnloaded['styles'])) {
                    foreach ($this->postTypesUnloaded['styles'] as $handleStyle) {
                        $list[] = $handleStyle;
                    }
                }
            }
        // [wpacu_lite]
        }
	    // [/wpacu_lite]

	    // Any bulk unloaded styles for 'category', 'post_tag' and more?
	    // If the premium extension is enabled, any of the unloaded CSS will be added to the list
	    $list = apply_filters('wpacu_filter_styles', array_unique($list));

        // Let's see if there are load exceptions for this page
        if (! empty($list) && ! empty($this->loadExceptions['styles'])) {
            foreach ($list as $handleKey => $handle) {
                if (in_array($handle, $this->loadExceptions['styles'])) {
                    unset($list[$handleKey]);
                }
            }
        }

	    global $wp_styles;

	    $allStyles = $wp_styles;

	    if (! empty($allStyles) && isset($allStyles->registered)) {
		    $i = $this->lastStylePos;

		    foreach ($allStyles->registered as $handle => $value) {
			    // This could be triggered several times, check if the style already exists
			    if (! isset($this->wpAllStyles['registered'][$handle])) {
				    $this->wpAllStyles['registered'][$handle] = $value;
				    $this->wpAllStyles['registered'][$handle]->wpacu_pos = $i;

				    $this->lastStylePos = $i;
				    $i++;

				    if (in_array($handle, $allStyles->queue)) {
					    $this->wpAllStyles['queue'][] = $handle;
				    }
			    }
		    }

		    if (isset($this->wpAllStyles['queue']) && ! empty($this->wpAllStyles['queue'])) {
			    $this->wpAllStyles['queue'] = array_unique( $this->wpAllStyles['queue'] );
		    }
	    }

	    // e.g. for test mode or AJAX calls (where all assets have to load)
	    if ($this->preventUnloadAssets() === true) {
	    	return;
	    }

	    if (empty($list)) {
		    return;
	    }

        foreach ($list as $handle) {
            $handle = trim($handle);

            wp_deregister_style($handle);
            wp_dequeue_style($handle);
        }
    }

    /**
     * @param string $type
     * @param string $postId
     * @return array|mixed|object
     */
    public function getLoadExceptions($type = 'post', $postId = '')
    {
        $exceptionsListDefault = $exceptionsList = $this->loadExceptions;

        if ($type === 'post' && !$postId) {
            // $postId needs to have a value if $type is a 'post' type
            return $exceptionsListDefault;
        }

        if (! $type) {
            // Invalid request
            return $exceptionsListDefault;
        }

        // Default
        $exceptionsListJson = '';

        $homepageClass = new HomePage;

        // Post or Post of the Homepage (if chosen in the Dashboard)
        if ($type === 'post'
            || ($homepageClass->data['show_on_front'] === 'page' && $postId)
        ) {
            $exceptionsListJson = get_post_meta(
                $postId, '_' . WPACU_PLUGIN_ID . '_load_exceptions',
                true
            );
        } elseif ($type === 'front_page') {
            // The home page could also be the list of the latest blog posts
            $exceptionsListJson = get_option(
	            WPACU_PLUGIN_ID . '_front_page_load_exceptions'
            );
        } elseif ($type === 'for_pro' && Main::wpacuProEnabled()) {
	        // [wpacu_pro]
        	if (class_exists( '\\WpAssetCleanUpPro\\LoadExceptions' )) {
		        $ExceptionsPro      = new \WpAssetCleanUpPro\LoadExceptions();
		        $exceptionsListJson = $ExceptionsPro->getLoadExceptions();
	        }
	        // [/wpacu_pro]
        }

        if ($exceptionsListJson) {
            $exceptionsList = json_decode($exceptionsListJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $exceptionsList = $exceptionsListDefault;
            }
        }

        /*
        if (! empty($_POST)) {
	        echo '<pre>';
	        print_r( $exceptionsList );
	        exit;
        }
        */

        return $exceptionsList;
    }

    /**
     * @return array
     */
    public function getGlobalUnload()
    {
        $existingListEmpty = array('styles' => array(), 'scripts' => array());
        $existingListJson  = get_option( WPACU_PLUGIN_ID . '_global_unload');

        $existingListData = $this->existingList($existingListJson, $existingListEmpty);

        return $existingListData['list'];
    }

	/**
	 * @param string $for (could be 'post_type', 'taxonomy' for premium extension etc.)
	 * @param string $type
	 *
	 * @return array
	 */
	public function getBulkUnload($for, $type = 'all')
    {
        $existingListEmpty = array('styles' => array(), 'scripts' => array());

        $existingListAllJson = get_option( WPACU_PLUGIN_ID . '_bulk_unload');

        if (! $existingListAllJson) {
            return $existingListEmpty;
        }

        $existingListAll = json_decode($existingListAllJson, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            return $existingListEmpty;
        }

        $existingList = $existingListEmpty;

        if (isset($existingListAll['styles'][$for][$type])
            && is_array($existingListAll['styles'][$for][$type])) {
            $existingList['styles'] = $existingListAll['styles'][$for][$type];
        }

        if (isset($existingListAll['scripts'][$for][$type])
            && is_array($existingListAll['scripts'][$for][$type])) {
            $existingList['scripts'] = $existingListAll['scripts'][$for][$type];
        }

        return $existingList;
    }

    /**
     *
     */
    public function saveFooterAssets()
    {
        global $wp_scripts;

        $this->assetsInFooter = $wp_scripts->in_footer;
    }

    /**
     * This output will be extracted and the JSON will be processed
     * in the WP Dashboard when editing a post
     *
     * It will also print the asset list in the front-end
     * if the option was enabled in the Settings
     */
    public function printScriptsStyles()
    {
    	// Not for WordPress AJAX calls
        if (self::$domGetType === 'direct' && defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        $isFrontEndEditView = $this->isFrontendEditView;
        $isDashboardEditView = (!$isFrontEndEditView && array_key_exists(WPACU_LOAD_ASSETS_REQ_KEY, $_REQUEST));

        if (!$isFrontEndEditView && !$isDashboardEditView) {
            return;
        }

        // Prevent plugins from altering the DOM
        add_filter('w3tc_minify_enable', '__return_false');

        // This is the list of the scripts an styles that were eventually loaded
        // We have also the list of the ones that were unloaded
        // located in $this->wpScripts and $this->wpStyles
        // We will add it to the list as they will be marked

        $stylesBeforeUnload = $this->wpAllStyles;
        $scriptsBeforeUnload = $this->wpAllScripts;

        global $wp_scripts, $wp_styles;

        $list = array();

        $currentUnloadedAll = $currentUnloaded = (array)json_decode(
            $this->getAssetsUnloaded($this->getCurrentPostId())
        );

        // Append global unloaded assets to current (one by one) unloaded ones
        if (! empty($this->globalUnloaded['styles'])) {
            foreach ($this->globalUnloaded['styles'] as $globalStyle) {
                $currentUnloadedAll['styles'][] = $globalStyle;
            }
        }

        if (! empty($this->globalUnloaded['scripts'])) {
            foreach ($this->globalUnloaded['scripts'] as $globalScript) {
                $currentUnloadedAll['scripts'][] = $globalScript;
            }
        }

        // Append bulk unloaded assets to current (one by one) unloaded ones
        if ($this->isSingularPage()) {
            if (! empty($this->postTypesUnloaded['styles'])) {
                foreach ($this->postTypesUnloaded['styles'] as $postTypeStyle) {
                    $currentUnloadedAll['styles'][] = $postTypeStyle;
                }
            }

            if (! empty($this->postTypesUnloaded['scripts'])) {
                foreach ($this->postTypesUnloaded['scripts'] as $postTypeScript) {
                    $currentUnloadedAll['scripts'][] = $postTypeScript;
                }
            }
        }

	    // [wpacu_pro]
	    $currentUnloadedAll = apply_filters('wpacu_pro_filter_all_bulk_unloads', $currentUnloadedAll);
	    // [/wpacu_pro]

	    $manageStyles = $wp_styles->done;
	    $manageScripts = $wp_scripts->done;

	    if ($isFrontEndEditView) {
	    	if (isset($this->wpAllStyles['queue']) && ! empty($this->wpAllStyles)) {
			    $manageStyles = $this->wpAllStyles['queue'];
		    }

		    if (isset($this->wpAllScripts['queue']) && ! empty($this->wpAllScripts)) {
			    $manageScripts = $this->wpAllScripts['queue'];
		    }

		    if (! empty($currentUnloadedAll['styles'])) {
			    foreach ( $currentUnloadedAll['styles'] as $currentUnloadedStyleHandle ) {
				    if ( ! in_array( $currentUnloadedStyleHandle, $manageStyles ) ) {
					    $manageStyles[] = $currentUnloadedStyleHandle;
				    }
			    }
		    }

		    if (! empty($wp_styles->done)) {
		    	foreach ($wp_styles->done as $wpDoneStyle) {
				    if ( ! in_array( $wpDoneStyle, $manageStyles ) ) {
					    $manageStyles[] = $wpDoneStyle;
				    }
			    }
		    }

		    $manageStyles = array_unique($manageStyles);

		    if (! empty($currentUnloadedAll['scripts'])) {
			    foreach ( $currentUnloadedAll['scripts'] as $currentUnloadedScriptHandle ) {
				    if ( ! in_array( $currentUnloadedScriptHandle, $manageScripts ) ) {
					    $manageScripts[] = $currentUnloadedScriptHandle;
				    }
			    }
		    }

		    if (! empty($wp_scripts->done)) {
			    foreach ($wp_scripts->done as $wpDoneScript) {
				    if ( ! in_array( $wpDoneScript, $manageScripts ) ) {
					    $manageScripts[] = $wpDoneScript;
				    }
			    }
		    }

		    $manageScripts = array_unique($manageScripts);
	    }

	    /*
		 * Style List
		 */
	    $stylesList = $wp_styles->registered;

	    if ($isFrontEndEditView) {
		    $stylesList = $stylesBeforeUnload['registered'];
	    }

        if (! empty($stylesList)) {
            /* These styles below are used by this plugin (except admin-bar) and they should not show in the list
               as they are loaded only when you (or other admin) manage the assets, never for your website visitors */
            $skipStyles = array(
                'admin-bar',
	            WPACU_PLUGIN_ID . '-style'
            );

            if (is_admin_bar_showing()) {
                $skipStyles[] = 'dashicons';
            }

            foreach ($manageStyles as $handle) {
                if (in_array($handle, $skipStyles) || (! isset($stylesList[$handle]))) {
                    continue;
                }

                $wpacuPos = isset($stylesBeforeUnload['registered'][$handle]->wpacu_pos)
                    ? $stylesBeforeUnload['registered'][$handle]->wpacu_pos
                    : '';

                if ($wpacuPos) {
                    $list['styles'][$wpacuPos] = $stylesList[$handle];
                } else {
                    $list['styles'][] = $stylesList[$handle];
                }
            }

            // Append unloaded ones (if any)
            if (! empty($currentUnloadedAll['styles']) && !empty($stylesBeforeUnload)) {
                foreach ($currentUnloadedAll['styles'] as $sbuHandle) {
                    if (! in_array($sbuHandle, $manageStyles)) {
                        // Could be an old style that is not loaded anymore
                        // We have to check that
                        if (! isset($stylesBeforeUnload['registered'][$sbuHandle])) {
                            continue;
                        }

                        $sbuValue = $stylesBeforeUnload['registered'][$sbuHandle];
                        $wpacuPos = $sbuValue->wpacu_pos;

                        $list['styles'][$wpacuPos] = $sbuValue;
                    }
                }
            }

            ksort($list['styles']);
        }

        /*
        * Scripts List
        */
	    $scriptsList = $wp_scripts->registered;

	    if ($isFrontEndEditView) {
		    $scriptsList = $scriptsBeforeUnload['registered'];
	    }

        if (! empty($scriptsList)) {
            /* These scripts below are used by this plugin (except admin-bar) and they should not show in the list
               as they are loaded only when you (or other admin) manage the assets, never for your website visitors */
            $skipScripts = array(
                'admin-bar',
	            WPACU_PLUGIN_ID . '-script'
            );

            foreach ($manageScripts as $handle) {
                if (in_array($handle, $skipScripts) || (! isset($scriptsList[$handle]))) {
                    continue;
                }

                $wpacuPos = isset($scriptsBeforeUnload['registered'][$handle]->wpacu_pos)
                    ? $scriptsBeforeUnload['registered'][$handle]->wpacu_pos
                    : '';

                if ($wpacuPos) {
                    $list['scripts'][$wpacuPos] = $scriptsList[$handle];
                } else {
                    $list['scripts'][] = $scriptsList[$handle];
                }
            }

            // Append unloaded ones (if any)
            if (! empty($currentUnloadedAll['scripts']) && !empty($scriptsBeforeUnload)) {
                foreach ($currentUnloadedAll['scripts'] as $sbuHandle) {
                    if (! in_array($sbuHandle, $manageScripts)) {
                        // Could be an old script that is not loaded anymore
                        // We have to check that
                        if (! isset($scriptsBeforeUnload['registered'][$sbuHandle])) {
                            continue;
                        }

                        $sbuValue = $scriptsBeforeUnload['registered'][$sbuHandle];
                        $wpacuPos = $sbuValue->wpacu_pos;

                        $list['scripts'][$wpacuPos] = $sbuValue;
                    }
                }
            }

            ksort($list['scripts']);
        }

        // Front-end View while admin is logged in
        if ($isFrontEndEditView) {
	        $wpacuSettings = new Settings();

            $data = array(
                'is_updateable'   => true,
                'post_type'       => '',
                'bulk_unloaded'   => array('post_type' => array()),
	            'plugin_settings' => $wpacuSettings->getAll()
            );

	        $data['wpacu_page_just_updated'] = false;

	        if (get_transient('wpacu_page_just_updated')) {
		        $data['wpacu_page_just_updated'] = true;
		        delete_transient('wpacu_page_just_updated');
	        }

	        // [wpacu_lite]
            if ($this->isUpdateable) {
            // [/wpacu_lite]
                $data['current'] = $currentUnloaded;

                $data['all']['scripts'] = $list['scripts'];
                $data['all']['styles']  = $list['styles'];

                $this->fetchUrl         = Misc::getPageUrl($this->getCurrentPostId());

                $data['fetch_url']      = $this->fetchUrl;

                $data['nonce_name']     = Update::NONCE_FIELD_NAME;
                $data['nonce_action']   = Update::NONCE_ACTION_NAME;

                $data = $this->alterAssetObj($data);

                $data['global_unload']   = $this->globalUnloaded;

                if (Misc::isHomePage()) {
                    $type = 'front_page';
                } elseif ($this->getCurrentPostId() > 0) {
                	$type = 'post';
                } else {
	                // [wpacu_pro]
                	// $this->getCurrentPostId() would be 0
                	$type = 'for_pro';
	                // [/wpacu_pro]
                }

                $data['load_exceptions'] = $this->getLoadExceptions($type, $this->getCurrentPostId());
            // [wpacu_lite]
            } else {
                $data['is_updateable'] = false;
            }
	        // [/wpacu_lite]

	        // WooCommerce Shop Page?
            $data['is_woo_shop_page'] = $this->vars['is_woo_shop_page'];

            $data['is_bulk_unloadable'] = $data['bulk_unloaded_type'] = false;

	        $data['bulk_unloaded']['post_type'] = array('styles' => array(), 'scripts' => array());

            if ($this->isSingularPage()) {
                $post = $this->getCurrentPost();

                // Current Post Type
                $data['post_type'] = $post->post_type;

                // Are there any assets unloaded for this specific post type?
                // (e.g. page, post, product (from WooCommerce) or other custom post type)
                $data['bulk_unloaded']['post_type'] = $this->getBulkUnload('post_type', $data['post_type']);

	            $data['bulk_unloaded_type'] = 'post_type';

	            $data['is_bulk_unloadable'] = true;

	            $data = $this->setPageTemplate($data);
            }

	        // [wpacu_pro]
            // If the premium extension is enabled, it will also pull the other bulk unloads
	        // such as 'taxonomy', 'author' etc.
            $data = apply_filters('wpacu_pro_get_bulk_unloads', $data);
	        // [/wpacu_pro]

            $data['total_styles']  = ! empty($data['all']['styles']) ? count($data['all']['styles']) : 0;
            $data['total_scripts'] = ! empty($data['all']['scripts']) ? count($data['all']['scripts']) : 0;

            $this->parseTemplate('settings-frontend', $data, true);
        } elseif ($isDashboardEditView) {
            // AJAX call (not the classic WP one) from the WP Dashboard
            echo self::START_DEL
                .base64_encode(json_encode($list)).
                self::END_DEL;

            // Do not allow further processes as cache plugins such as W3 Total Cache could alter the source code
            // and we need the non-minified version of the DOM (e.g. to determine the position of the elements)
            exit();
        }
    }

    /**
     * @param $name
     * @param array $data (if present $data values are used within the included template)
     * @param bool|false $echo
     * @return bool|string
     */
    public function parseTemplate($name, $data = array(), $echo = false)
    {
        $templateFile = apply_filters(
            'wpacu_template_file', // tag
            dirname(__DIR__) . '/templates/' . $name . '.php', // value
            $name // extra argument
        );

        if (! file_exists($templateFile)) {
            wp_die('Template '.$name.' not found.');
        }

        ob_start();
        include $templateFile;
        $result = ob_get_clean();

        if ($echo) {
            echo $result;
            return true;
        }

        return $result;
    }

    /**
     *
     */
    public function ajaxGetJsonListCallback()
    {
        $postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : ''; // if any (could be home page for instance)
        $pageUrl = Misc::getVar('post', 'page_url'); // post, page, custom post type, home page etc.

        $wpacuList = $contents = '';

        $settings = new Settings();

        if (self::$domGetType === 'direct') {
            $contents = Misc::getVar('post', 'contents');
            $wpacuList = Misc::getVar('post', 'wpacu_list');
        } elseif (self::$domGetType === 'wp_remote_post') {
	        $wpRemotePost = wp_remote_post($pageUrl, array(
                'body' => array(
	                WPACU_LOAD_ASSETS_REQ_KEY => 1
                )
            ));

            $contents = isset($wpRemotePost['body']) ? $wpRemotePost['body'] : '';

            if ($contents
                && (strpos($contents, self::START_DEL) !== false)
                && (strpos($contents, self::END_DEL) !== false)) {
                $wpacuList = Misc::extractBetween(
                    $contents,
                    self::START_DEL,
                    self::END_DEL
                );
            }

            // The list of assets could not be retrieved via "WP Remove Post" for this server
	        // Print out the response to make the user aware about it
            if (! $wpacuList) {
            	$data = array(
            		'is_dashboard_view' => true,
            		'plugin_settings'   => $settings->getAll(),
            		'wp_remote_post'    => $wpRemotePost,
	            );

	            $this->parseTemplate('meta-box-loaded', $data, true);
	            exit;
            }
        }

        $json = base64_decode($wpacuList);

        $data = array(
        	'post_id'         => $postId,
	        'plugin_settings' => $settings->getAll()
        );

        $data['all'] = (array)json_decode($json);

        // This value is needed to determine the location of an asset (HEAD OR BODY)
        if ($contents !== '') {
            $data['contents'] = base64_decode($contents);
        }

        $data = $this->alterAssetObj($data);

        // Check any existing results
        $data['current'] = (array)json_decode($this->getAssetsUnloaded($postId));

        // Set to empty if not set to avoid any errors
        if (! isset($data['current']['styles']) || !is_array($data['current']['styles'])) {
            $data['current']['styles'] = array();
        }

        if (! isset($data['current']['scripts']) || !is_array($data['current']['scripts'])) {
            $data['current']['scripts'] = array();
        }

        $data['fetch_url'] = $pageUrl;
        $data['global_unload'] = $this->getGlobalUnload();

        $data['is_bulk_unloadable'] = $data['bulk_unloaded_type'] = false;

        // Post Information
	    if ($postId > 0) {
		    $postData = get_post($postId);

		    if (isset($postData->post_type)) {
			    // Current Post Type
			    $data['post_type'] = $postData->post_type;

			    // Are there any assets unloaded for this specific post type?
			    // (e.g. page, post, product (from WooCommerce) or other custom post type)
			    $data['bulk_unloaded']['post_type'] = $this->getBulkUnload('post_type', $data['post_type']);
			    $data['bulk_unloaded_type']         = 'post_type';
			    $data['is_bulk_unloadable']         = true;
		    }
	    }

	    // [wpacu_pro]
	    // If the pro version is used, it will also pull the other bulk unloads such as 'taxonomy', 'author' etc.
	    $data = apply_filters('wpacu_pro_get_bulk_unloads', $data);
		// [/wpacu_pro]

	    // For debug purposes
	    //unset($data['contents']); echo '<pre>'; print_r($data); exit;

        //echo '<pre>'; print_r($data['bulk_unloaded']['post_type']);
		if ($postId > 0) {
			$type = 'post';
		} elseif (Misc::getVar('post', 'tag_id')) {
			// [wpacu_pro]
			$type = 'for_pro';
			// [/wpacu_pro]
		} elseif($postId == 0) {
			$type = 'front_page';
		}

        $data['load_exceptions'] = $this->getLoadExceptions($type, $postId);

        $data['total_styles']  = ! empty($data['all']['styles']) ? count($data['all']['styles']) : 0;
        $data['total_scripts'] = ! empty($data['all']['scripts']) ? count($data['all']['scripts']) : 0;

        $this->parseTemplate('meta-box-loaded', $data, true);

        exit;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function alterAssetObj($data)
    {
        $siteUrl = get_site_url();

        if (! empty($data['all']['styles'])) {
            $data['core_styles_loaded'] = false;

            foreach ($data['all']['styles'] as $key => $obj) {
                if (! isset($obj->handle)) {
                    unset($data['all']['styles']['']);
                    continue;
                }

                if (isset($obj->src, $data['all']['styles'][$key]) && $obj->src) {
                    $part = str_replace(
                        array(
                            'http://',
                            'https://',
                            '//'
                        ),
                        '',
                        $obj->src
                    );

                    list(,$parentDir) = explode('/', $part);

                    // Loaded from WordPress directories (Core)
                    if (in_array($parentDir, array('wp-includes', 'wp-admin'))) {
                        $data['all']['styles'][$key]->wp = true;
                        $data['core_styles_loaded'] = true;
                    }

                    // Determine source href
                    if (substr($obj->src, 0, 1) === '/' && substr($obj->src, 0, 2) !== '//') {
                        $obj->srcHref = $siteUrl . $obj->src;
                    } else {
                        $obj->srcHref = $obj->src;
                    }
                }
            }
        }

        if (! empty($data['all']['scripts'])) {
            $data['core_scripts_loaded'] = false;

            $headPart = $bodyPart = '';

            if (isset($data['contents'])) {
                // Extract 'HEAD' part
                $headPart = Misc::extractBetween($data['contents'], '<head', '</head>');

                // Extract 'BODY' part
                $contentsAltered = str_replace($headPart, '', $data['contents']);
                $bodyDel = '<body'; // Get everything after $bodyDel
                $bodyPart = substr($data['contents'], stripos($contentsAltered, $bodyDel) + strlen($bodyDel));
            }

            foreach ($data['all']['scripts'] as $key => $obj) {
                if (! isset($obj->handle)) {
                    unset($data['all']['scripts']['']);
                    continue;
                }

                // From WordPress directories (false by default)
                $data['all']['scripts'][$key]->wp = false;

                $toCheck = $obj->src;
                $toCheckExtra = str_replace(
                    array(',', '&'),
                    array('%2C', '&#038;'),
                    $obj->src
                );

                if (isset($data['contents'])) {
                    if (stripos($headPart, $toCheck) !== false || stripos($headPart, $toCheckExtra) !== false) {
                        $data['all']['scripts'][$key]->position = 'head';
                    } elseif (stripos($bodyPart, $toCheck) !== false || stripos($bodyPart, $toCheckExtra) !== false) {
                        $data['all']['scripts'][$key]->position = 'body';
                    }
                } elseif (in_array($obj->handle, $this->assetsInFooter)) {
                    $data['all']['scripts'][$key]->position = 'body';
                } else {
                    $data['all']['scripts'][$key]->position = 'head';
                }

                if (isset($data['all']['scripts'][$key])) {
                    if (isset($obj->src) && $obj->src) {
                        $part = str_replace(
                            array(
                                'http://',
                                'https://',
                                '//'
                            ),
                            '',
                            $obj->src
                        );

                        list(,$parentDir) = explode('/', $part);

                        // Loaded from WordPress directories (Core)
                        if (in_array($parentDir, array('wp-includes', 'wp-admin'))) {
                            $data['all']['scripts'][$key]->wp = true;
                            $data['core_scripts_loaded'] = true;
                        }

                        // Determine source href
                        if (substr($obj->src, 0, 1) === '/' && substr($obj->src, 0, 2) !== '//') {
                            $obj->srcHref = $siteUrl . $obj->src;
                        } else {
                            $obj->srcHref = $obj->src;
                        }
                    }

                    if ($obj->handle === 'jquery') {
                        $data['all']['scripts'][$key]->wp = true;
                        $data['core_scripts_loaded'] = true;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * This method retrieves only the assets that are unloaded per page
     * Including 404, date and search pages (they are considered as ONE page with the same rules for any URL variation)
     *
     * @param int $postId
     * @return string (The returned value must be a JSON one)
     */
    public function getAssetsUnloaded($postId = 0)
    {
        // Post Type (Overwrites 'front' - home page - if we are in a singular post)
        if ($postId == 0) {
            $postId = $this->getCurrentPostId();
        }

        $isInAdminPageViaAjax = (is_admin() && defined('DOING_AJAX') && DOING_AJAX);

        if (empty($this->assetsRemoved)) {
            // For Home Page (latest blog posts)
            if ($postId < 1 && ($isInAdminPageViaAjax || Misc::isHomePage())) {
                $this->assetsRemoved = get_option( WPACU_PLUGIN_ID . '_front_page_no_load');
            } elseif ($postId > 0) {
                $this->assetsRemoved = get_post_meta($postId, '_' . WPACU_PLUGIN_ID . '_no_load', true);
            }

	        // [wpacu_pro]
	        // Premium Extension: Filter assets for pages such as category, tag, author, dates etc.
	        // Retrieves "per page" list of unloaded CSS and JavaScript
            $this->assetsRemoved = apply_filters('wpacu_pro_get_assets_unloaded', $this->assetsRemoved);
	        // [/wpacu_pro]

	        @json_decode($this->assetsRemoved);

	        if (! (json_last_error() === JSON_ERROR_NONE) || empty($this->assetsRemoved)) {
	        	// Reset value to a JSON formatted one
		        $this->assetsRemoved = json_encode(array('styles' => array(), 'scripts' => array()));
	        }
        }

        return $this->assetsRemoved;
    }

    /**
     * @return bool
     */
    public function isSingularPage()
    {
        return ($this->vars['is_woo_shop_page'] || is_singular());
    }

    /**
     * @return int|mixed|string
     */
    public function getCurrentPostId()
    {
        if ($this->currentPostId > 0) {
            return $this->currentPostId;
        }

        // Are we on the `Shop` page from WooCommerce?
        // Only check option if function `is_shop` exists
        $wooCommerceShopPageId = function_exists('is_shop') ? get_option('woocommerce_shop_page_id') : 0;

        // Check if we are on the WooCommerce Shop Page
        // Do not mix the WooCommerce Search Page with the Shop Page
        if (function_exists('is_shop') && is_shop()) {
            $this->currentPostId = $wooCommerceShopPageId;

            if ($this->currentPostId > 0) {
                $this->vars['is_woo_shop_page'] = true;
            }
        } else {
            if ($wooCommerceShopPageId > 0 && Misc::isHomePage()) {
                if (strpos(get_site_url(), '://') !== false) {
                    list($siteUrlAfterProtocol) = explode('://', get_site_url());
                    $currentPageUrlAfterProtocol = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

                    if ($siteUrlAfterProtocol != $currentPageUrlAfterProtocol && (strpos($siteUrlAfterProtocol,
                                '/shop') !== false)
                    ) {
                        $this->vars['woo_url_not_match'] = true;
                    }
                }
            }
        }

	    // Blog Home Page (aka: Posts page) is not a singular page, it's checked separately
        if (Misc::isBlogPage()) {
        	$this->currentPostId = get_option('page_for_posts');
        }

        // It has to be a single page (no "Posts page")
        if (is_singular() && ($this->currentPostId < 1)) {
            global $post;
            $this->currentPostId = isset($post->ID) ? $post->ID : 0;
        }

	    // [wpacu_lite]
        // Undetectable? The page is not a singular one nor the home page
        // It's likely an archive, category page (WooCommerce), 404 page etc.
        if (! $this->currentPostId && ! Misc::isHomePage()) {
        	// Check if "Asset CleanUp Pro" is enabled
	        // Archives, tags, categories (taxonomy) pages are available in the premium extension: Asset CleanUp Pro
	        if ($this->wpacuProEnabled()) {
	        	// Could be archive of: Category, Tag, Author, Date, Custom Post Type or Custom Taxonomy based pages.
		        // Or: Search, 404 page etc.
		        $this->isUpdateable = true;
	        } else {
		        $this->isUpdateable = false;
	        }
        }

	    // [/wpacu_lite]

        return $this->currentPostId;
    }

    /**
     * @return array|null|\WP_Post
     */
    public function getCurrentPost()
    {
        // Already set? Return it
        if (! empty($this->currentPost)) {
            return $this->currentPost;
        }

        // Not set? Create and return it
        if (! $this->currentPost && $this->getCurrentPostId() > 0) {
            $this->currentPost = get_post($this->getCurrentPostId());
            return $this->currentPost;
        }

        // Empty
        return $this->currentPost;
    }

	/**
	 * @param $data
	 *
	 * @return mixed
	 */
	public function setPageTemplate($data)
    {
    	global $template;

	    $getPageTpl = get_post_meta($this->getCurrentPostId(), '_wp_page_template', true);

	    // Could be a custom post type with no template set
	    if (! $getPageTpl) {
		    $getPageTpl = get_page_template();

		    if (in_array(basename($getPageTpl), array('single.php', 'page.php'))) {
			    $getPageTpl = 'default';
		    }
	    }

	    if (! $getPageTpl) {
	    	return $data;
	    }

	    $data['page_template'] = $getPageTpl;

	    $data['all_page_templates'] = wp_get_theme()->get_page_templates();

	    // Is the default template shown? Most of the time it is!
	    if ($data['page_template'] === 'default') {
	    	$pageTpl = (isset($template) && $template) ? $template : get_page_template();
		    $data['page_template'] = basename( $pageTpl );
		    $data['all_page_templates'][ $data['page_template'] ] = 'Default Template';
	    }

	    if (isset($template) && $template && defined('ABSPATH')) {
	    	$data['page_template_path'] = str_replace(
			    ABSPATH,
			    '',
			    '/'.$template
		    );
	    }

	    return $data;
    }

    /**
     * @return bool
     */
    public static function isSettingsPage()
    {
        return (array_key_exists('page', $_GET) && $_GET['page'] === WPACU_PLUGIN_ID . '_settings');
    }

	/**
	 *
	 */
	public function doDisableEmojis()
    {
    	if ($this->preventUnloadAssets()) {
	        return;
	    }

	    // Emojis Actions and Filters
	    remove_action('admin_print_styles', 'print_emoji_styles');
	    remove_action('wp_head', 'print_emoji_detection_script', 7);
	    remove_action('admin_print_scripts', 'print_emoji_detection_script');
	    remove_action('wp_print_styles', 'print_emoji_styles');

	    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	    remove_filter('the_content_feed', 'wp_staticize_emoji');
	    remove_filter('comment_text_rss', 'wp_staticize_emoji');

	    // TinyMCE Emojis
	    add_filter('tiny_mce_plugins', array($this, 'removeEmojisTinymce'));

	    add_filter('emoji_svg_url', '__return_false');
    }

	/**
	 * @param $plugins
	 *
	 * @return array
	 */
	public function removeEmojisTinymce($plugins)
    {
	    if (is_array($plugins)) {
		    return array_diff($plugins, array('wpemoji'));
	    }

	    return array();
    }

	/**
	 * @return bool
	 */
	public static function isWpDefaultSearchPage()
	{
		// It will not interfere with the WooCommerce search page
		// which is considered to be the "Shop" page that has its own unload rules
		return (is_search() && (! (function_exists('is_shop') && is_shop())));
	}

	/**
	 * @param $existingListJson
	 * @param $existingListEmpty
	 *
	 * @return array
	 */
	public function existingList($existingListJson, $existingListEmpty)
	{
		$validJson = $notEmpty = true;

		if (! $existingListJson) {
			$existingList = $existingListEmpty;
			$notEmpty = false;
		} else {
			$existingList = json_decode($existingListJson, true);

			if (json_last_error() !== JSON_ERROR_NONE) {
				$validJson = false;
				$existingList = $existingListEmpty;
			}
		}

		return array(
			'list'       => $existingList,
			'valid_json' => $validJson,
			'not_empty'  => $notEmpty
		);
	}

	/**
	 * Situations when the assets will not be prevented from loading
	 * e.g. test mode and a visitor accessing the page, an AJAX request from the Dashboard to print all the assets
	 * @return bool
	 */
	public function preventUnloadAssets()
	{
		// This request specifically asks for all the assets to be loaded in order to print them in the assets management list
		// This is for the AJAX requests within the Dashboard, thus the admin needs to see all the assets,
		// including ones marked for unload, in case he/she decides to change their rules
		if (isset($_REQUEST[WPACU_LOAD_ASSETS_REQ_KEY])) {
			return true;
		}

		// Is test mode enabled? Unload assets ONLY for the admin
		if (isset($this->settings['test_mode']) && $this->settings['test_mode'] && ! Menu::userCanManageAssets()) {
			return true; // visitors (non-logged in) will view the pages with all the assets loaded
		}

		return false;
	}

	// [wpacu_pro]
	/**
	 * @return bool
	 */
	public function wpacuProEnabled()
    {
    	return defined('WPACU_PRO_PLUGIN_FILE');
    }
	// [/wpacu_pro]

	// [wpacu_lite]
	/**
	 *
	 */
	public function wpacuUsageNotice()
	{
		add_action('wp_loaded', function() {
			ob_start(function($htmlSource) {
				// If user is within the Dashboard or logged in (could be in the front-end view)
				// do not show the usage notice, spread the word for new visitors
				if (is_admin() || is_user_logged_in()) {
					return $htmlSource;
				}

				$altCleanHtmlSource = trim($htmlSource);

				if (strtolower(substr($altCleanHtmlSource, -7)) === '</html>') {
					$extraParams = '';

					if (defined('WP_CONTENT_URL')) {
						$urlPieces = parse_url( WP_CONTENT_URL );
						$shortSource = isset($urlPieces['host']) ? str_ireplace('www.', '', $urlPieces['host']) : '';

						if (strpos($shortSource, '.') !== false) {
							list($shortSource) = explode('.', $shortSource);
						}

						if ($shortSource) {
							$extraParams = '?utm_source=' . $shortSource . '&utm_medium=website_html_comment';
						}
					}

					$htmlSource .= "\n" . '<!-- This website is optimized by Asset CleanUp: Page Speed Booster. Do you want to have a faster loading website? Learn more here: https://wordpress.org/plugins/wp-asset-clean-up/'.$extraParams.' -->';
				}

				return $htmlSource;
			});
		});
	}
	// [wpacu_lite]

	/**
	 * Make administrator more aware if "TEST MODE" is enabled or not
	 */
	public function wpacuHtmlNoticeForAdmin()
	{
		add_action('wp_loaded', function() {
			ob_start(function($htmlSource) {
				if ( ! (Menu::userCanManageAssets() && ! is_admin())) {
					return $htmlSource;
				}

				$altCleanHtmlSource = trim($htmlSource);

				if (strtolower(substr($altCleanHtmlSource, -7)) === '</html>') {
					if (Main::instance()->settings['test_mode']) {
						$consoleMessage = __('Asset CleanUp: "TEST MODE" ENABLED (any settings or unloads will be visible ONLY to you, the logged-in administrator)', WPACU_PLUGIN_TEXT_DOMAIN);
						$testModeNotice = __('"Test Mode" is ENABLED. Any settings or unloads will be visible ONLY to you, the logged-in administrator.', WPACU_PLUGIN_TEXT_DOMAIN);
					} else {
						$consoleMessage = __('Asset CleanUp: "LIVE MODE" (test mode is not enabled, thus, all the plugin changes are visible for everyone: you, the logged-in administrator and the regular visitors)', WPACU_PLUGIN_TEXT_DOMAIN);
						$testModeNotice = __('The website is in LIVE MODE as "Test Mode" is not enabled. All the plugin changes are visible for everyone: logged-in administrators and regular visitors.', WPACU_PLUGIN_TEXT_DOMAIN);
					}

					$htmlCommentNote = __('NOTE: These "Asset CleanUp: Page Speed Booster" messages are only shown to you, the HTML comment is not visible for the regular visitor.', WPACU_PLUGIN_TEXT_DOMAIN);

					$htmlSource .= <<<HTML
<!--
{$htmlCommentNote}

{$testModeNotice}
-->
<script type="text/javascript">
console.log('{$consoleMessage}');
</script>
HTML;
				}

				return $htmlSource;
			});
		});
	}
}
