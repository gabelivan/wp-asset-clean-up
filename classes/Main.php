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
    const STARTDEL = '@ BEGIN WPACU PLUGIN JSON @';

    /**
     *
     */
    const ENDDEL = '@ END WPACU PLUGIN JSON @';

    /**
     * @var array
     */
    public $assetsRemoved = array();

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

    /**
     * @var bool|mixed|void
     */
    public $frontendShow = false;

    /**
     * @var array
     */
    public $assetsInFooter = array();

    /**
     * @var array
     */
    public $wpScripts = array();

    /**
     * @var array
     */
    public $wpStyles = array();

    /**
     * @var array
     */
    public $postTypesUnloaded = array();

    /**
     * @var Main|null
     */
    private static $singleton = null;

    /**
     * @return null|Main
     */
    public static function instance()
    {
        if (is_null(self::$singleton)) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }

    /**
     * Parser constructor.
     */
    public function __construct()
    {
        $this->frontendShow = (get_option(WPACU_PLUGIN_NAME.'_frontend_show'));

        // Early Triggers
        add_action('wp', array($this, 'setVars'), 2);

        // Fetch the page in the background to see what scripts/styles are already loading
        if (isset($_POST[WPACU_PLUGIN_NAME.'_load']) || $this->frontendShow) {
            if (isset($_POST[WPACU_PLUGIN_NAME.'_load'])) {
                Misc::noAdminBarLoad();
            }

            add_action('wp_head', array($this, 'saveFooterAssets'), 100000000);
            add_action('wp_footer', array($this, 'printScriptsStyles'), 100000000);
        }

        // Front-end View - Unload the assets
        if (! isset($_POST[WPACU_PLUGIN_NAME.'_load'])) {
            // Unload Styles - HEAD
            add_action('wp_print_styles', array($this, 'filterStyles'), 100000);

            // Unload Scripts - HEAD
            add_action('wp_print_scripts', array($this, 'filterScripts'), 100000);

            // Unload Scripts & Styles - FOOTER
            // Needs to be triggered very soon as some old plugins/themes use wp_footer() to enqueue scripts
            // Sometimes styles are loaded in the BODY section of the page
            add_action('wp_print_footer_scripts', array($this, 'filterScripts'), 1);
            add_action('wp_print_footer_scripts', array($this, 'filterStyles'), 1);
        }

        // Send an AJAX request to get the list of loaded scripts and styles and print it nicely
        add_action(
            'wp_ajax_'. WPACU_PLUGIN_NAME . '_get_loaded_assets',
            array($this, 'ajaxGetJsonListCallback')
        );

        add_action('add_meta_boxes', array($this, 'addMetaBox'));
    }

    /**
     *
     */
    public function setVars()
    {
        if (! isset($_POST[WPACU_PLUGIN_NAME.'_load'])) {
            $this->globalUnloaded = $this->getGlobalUnload();

            if (! is_singular() && ! Misc::isHomePage()) {
                return;
            }

            global $post;

            $postId = isset($post->ID) ? $post->ID : '';

            $type = (Misc::isHomePage()) ? 'front_page' : 'post';

            if (is_singular()) {
                $this->postTypesUnloaded = $this->getPostTypeUnload($post->post_type);
            }

            $this->loadExceptions = $this->getLoadExceptions($type, $postId);
        }
    }

    /**
     * @param $postType
     */
    public function addMetaBox($postType)
    {
        $obj = get_post_type_object($postType);

        if ($obj->public > 0) {
            add_meta_box(
                WPACU_PLUGIN_NAME.'_asset_list',
                __('WP Asset CleanUp', WPACU_PLUGIN_NAME),
                array($this, 'renderMetaBoxContent'),
                $postType,
                'advanced',
                'high'
            );
        }
    }

    /**
     *
     */
    public function renderMetaBoxContent()
    {
        global $post;

        if (! isset($post->ID)) {
            return;
        }

        $postId = $post->ID;

        $getAssets = true;

        if (get_post_status($postId) != 'publish') {
            $getAssets = false;
        }

        if ($getAssets) {
            // Add an nonce field so we can check for it later.
            wp_nonce_field(WPACU_PLUGIN_NAME . '_meta_box', WPACU_PLUGIN_NAME . '_nonce');
        }

        $data = array();

        $data['get_assets'] = $getAssets;

        $data['fetch_url'] = Misc::getPostUrl($postId);

        $this->parseTemplate('meta-box', $data, true);
    }

    /**
     * See if there is any list with scripts to be removed in JSON format
     * Only the handles (the ID of the scripts) is stored
     */
    public function filterScripts()
    {
        if (is_admin()) {
            return;
        }

        $nonAssetConfigPage = (! is_singular() && ! Misc::getShowOnFront());

        // It looks like the page loaded is neither a post, page or the front-page
        // We'll see if there are assets unloaded globally and unload them
        $globalUnload = $this->globalUnloaded;

        if (! empty($globalUnload['scripts']) && $nonAssetConfigPage) {
            $list = $globalUnload['scripts'];
        } else {
            // Post, Page or Front-page?
            $toRemove = $this->getAssetsUnloaded();

            // if null or array (string has to be returned)
            if (! $toRemove || is_array($toRemove)) {
                return;
            }

            $jsonList = @json_decode($toRemove);

            if (json_last_error()) {
                return;
            }

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

            if (is_singular()) {
                // Any bulk unloaded styles (e.g. for all pages belonging to a post type)? Append them
                if (empty($this->postTypesUnloaded)) {
                    global $post;
                    $this->postTypesUnloaded = $this->getPostTypeUnload($post->post_type);
                }

                if (!empty($this->postTypesUnloaded['scripts'])) {
                    foreach ($this->postTypesUnloaded['scripts'] as $handleStyle) {
                        $list[] = $handleStyle;
                    }
                }
            }

            $list = array_unique($list);
        }

        // Let's see if there are load exceptions for this page
        if (! empty($list) && ! empty($this->loadExceptions['scripts'])) {
            foreach ($list as $handleKey => $handle) {
                if (in_array($handle, $this->loadExceptions['scripts'])) {
                    unset($list[$handleKey]);
                }
            }
        }

        if (empty($list)) {
            return;
        }

        global $wp_scripts;

        // Only fill it once
        if (empty($this->wpScripts)) {
            $this->wpScripts = (array)$wp_scripts;

            if (! empty($this->wpScripts) && isset($this->wpScripts['registered'])) {
                $i = 1;

                foreach ($this->wpScripts['registered'] as $handle => $value) {
                    $this->wpScripts['registered'][$handle]->wpacu_pos = $i;
                    $i++;
                }
            }
        }

        foreach ($list as $handle) {
            $handle = trim($handle);

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

        $nonAssetConfigPage = (! is_singular() && ! Misc::getShowOnFront());

        // It looks like the page loaded is neither a post, page or the front-page
        // We'll see if there are assets unloaded globally and unload them
        $globalUnload = $this->globalUnloaded;

        if (! empty($globalUnload['styles']) && $nonAssetConfigPage) {
            $list = $globalUnload['styles'];
        } else {
            // Post, Page or Front-page
            $toRemove = $this->getAssetsUnloaded();

            // if null or array (string has to be returned)
            if (! $toRemove || is_array($toRemove)) {
                return;
            }

            $jsonList = @json_decode($toRemove);

            if (json_last_error()) {
                return;
            }

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

            if (is_singular()) {
                // Any bulk unloaded styles (e.g. for all pages belonging to a post type)? Append them
                if (empty($this->postTypesUnloaded)) {
                    global $post;
                    $this->postTypesUnloaded = $this->getPostTypeUnload($post->post_type);
                }

                if (!empty($this->postTypesUnloaded['styles'])) {
                    foreach ($this->postTypesUnloaded['styles'] as $handleStyle) {
                        $list[] = $handleStyle;
                    }
                }
            }

            $list = array_unique($list);
        }

        // Let's see if there are load exceptions for this page
        if (! empty($list) && ! empty($this->loadExceptions['styles'])) {
            foreach ($list as $handleKey => $handle) {
                if (in_array($handle, $this->loadExceptions['styles'])) {
                    unset($list[$handleKey]);
                }
            }
        }

        if (empty($list)) {
            return;
        }

        global $wp_styles;

        // Only fill it once
        if (empty($this->wpStyles)) {
            $this->wpStyles = (array)$wp_styles;

            if (! empty($this->wpStyles) && isset($this->wpStyles['registered'])) {
                $i = 1;

                foreach ($this->wpStyles['registered'] as $handle => $value) {
                    $this->wpStyles['registered'][$handle]->wpacu_pos = $i;
                    $i++;
                }
            }
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

        if ($type == 'post' && !$postId) {
            // $postId needs to have a value if $type is a 'post' type
            return $exceptionsListDefault;
        }

        if (! in_array($type, array('post', 'front_page'))) {
            // Invalid request
            return $exceptionsListDefault;
        }

        // Default
        $exceptionsListJson = '';

        $homepageClass = new HomePage;

        // Post or Post of the Homepage (if chosen in the Dashboard)
        if ($type == 'post'
            || $homepageClass->data['show_on_front'] === 'page'
        ) {
            $exceptionsListJson = get_post_meta(
                $postId, '_' . WPACU_PLUGIN_NAME . '_load_exceptions',
                true
            );
        } elseif ($type == 'front_page') {
            // The home page could also be the list of the latest blog posts
            $exceptionsListJson = get_option(
                WPACU_PLUGIN_NAME . '_front_page_load_exceptions'
            );
        }

        if ($exceptionsListJson) {
            $exceptionsList = json_decode($exceptionsListJson, true);

            if (json_last_error() != JSON_ERROR_NONE) {
                $exceptionsList = $exceptionsListDefault;
            }
        }

        return $exceptionsList;
    }

    /**
     * @return array
     */
    public function getGlobalUnload()
    {
        $existingListEmpty = array('styles' => array(), 'scripts' => array());

        $existingListJson = get_option(WPACU_PLUGIN_NAME.'_global_unload');

        if (! $existingListJson) {
            return $existingListEmpty;
        }

        $existingList = json_decode($existingListJson, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            $existingList = $existingListEmpty;
        }

        return $existingList;
    }

    /**
     * @param $postType
     * @return array
     */
    public function getPostTypeUnload($postType)
    {
        $existingListEmpty = array();

        $existingListAllJson = get_option(WPACU_PLUGIN_NAME.'_bulk_unload');

        if (! $existingListAllJson) {
            return $existingListEmpty;
        }

        $existingListAll = json_decode($existingListAllJson, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            return $existingListEmpty;
        }

        $existingList = array();

        if (isset($existingListAll['styles']['post_type'][$postType])
            && is_array($existingListAll['styles']['post_type'][$postType])) {
            $existingList['styles'] = $existingListAll['styles']['post_type'][$postType];
        }

        if (isset($existingListAll['scripts']['post_type'][$postType])
            && is_array($existingListAll['scripts']['post_type'][$postType])) {
            $existingList['scripts'] = $existingListAll['scripts']['post_type'][$postType];
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
        if (defined('DOING_AJAX') && DOING_AJAX) {
            // Not for AJAX calls
            return;
        }

        global $post;

        $isFrontEndView = ($this->frontendShow && current_user_can('manage_options')
            && !isset($_POST[WPACU_PLUGIN_NAME.'_load'])
            && !is_admin());
        $isDashboardView = (!$isFrontEndView && isset($_POST[WPACU_PLUGIN_NAME.'_load']));

        if (!$isFrontEndView && !$isDashboardView) {
            return;
        }

        // This is the list of the scripts an styles that were eventually loaded
        // We have also the list of the ones that were unloaded
        // located in $this->wpScripts and $this->wpStyles
        // We will add it to the list as they will be marked

        $stylesBeforeUnload = $this->wpStyles;
        $scriptsBeforeUnload = $this->wpScripts;



        global $wp_scripts, $wp_styles;

        $list = array();

        $currentUnloadedAll = $currentUnloaded = (array)json_decode(
            $this->getAssetsUnloaded($post->ID)
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
        if (is_singular()) {
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

        /*
         * Style List
         */
        if (! empty($wp_styles)) {
            /* These styles below are used by this plugin (except admin-bar) and they should not show in the list
               as they are loaded only when you (or other admin) manage the assets, never for your website visitors */
            $skipStyles = array(
                'admin-bar',
                WPACU_PLUGIN_NAME . '-icheck-square-red',
                WPACU_PLUGIN_NAME . '-style'
            );

            foreach ($wp_styles->done as $handle) {
                if ($isFrontEndView && in_array($handle, $skipStyles)) {
                    continue;
                }

                $wpacuPos = isset($stylesBeforeUnload['registered'][$handle]->wpacu_pos)
                    ? $stylesBeforeUnload['registered'][$handle]->wpacu_pos
                    : '';

                if ($wpacuPos) {
                    $list['styles'][$wpacuPos] = $wp_styles->registered[$handle];
                } else {
                    $list['styles'][] = $wp_styles->registered[$handle];
                }
            }

            // Append unloaded ones (if any)
            if (! empty($currentUnloadedAll['styles']) && !empty($stylesBeforeUnload)) {
                foreach ($currentUnloadedAll['styles'] as $sbuHandle) {
                    if (!in_array($sbuHandle, $wp_styles->done)) {
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
        if (! empty($wp_scripts)) {
            /* These scripts below are used by this plugin (except admin-bar) and they should not show in the list
               as they are loaded only when you (or other admin) manage the assets, never for your website visitors */
            $skipScripts = array(
                'admin-bar',
                WPACU_PLUGIN_NAME . '-icheck',
                WPACU_PLUGIN_NAME.'-script'
            );

            foreach ($wp_scripts->done as $handle) {
                if ($isFrontEndView && in_array($handle, $skipScripts)) {
                    continue;
                }

                $wpacuPos = isset($scriptsBeforeUnload['registered'][$handle]->wpacu_pos)
                    ? $scriptsBeforeUnload['registered'][$handle]->wpacu_pos
                    : '';

                if ($wpacuPos) {
                    $list['scripts'][$wpacuPos] = $wp_scripts->registered[$handle];
                } else {
                    $list['scripts'][] = $wp_scripts->registered[$handle];
                }
            }

            // Append unloaded ones (if any)
            if (! empty($currentUnloadedAll['scripts']) && !empty($scriptsBeforeUnload)) {
                foreach ($currentUnloadedAll['scripts'] as $sbuHandle) {
                    if (!in_array($sbuHandle, $wp_scripts->done)) {
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
        if ($isFrontEndView) {
            $data = array();

            $data['current'] = $currentUnloaded;

            $data['all']['scripts'] = $list['scripts'];
            $data['all']['styles'] = $list['styles'];

            $this->fetchUrl = Misc::getPostUrl($post->ID);

            $data['fetch_url'] = $this->fetchUrl;

            $data['nonce_name'] = Update::NONCE_FIELD_NAME;
            $data['nonce_action'] = Update::NONCE_ACTION_NAME;

            $data = $this->alterAssetObj($data);

            $data['global_unload'] = $this->globalUnloaded;

            $type = Misc::getShowOnFront() ? 'front_page' : 'post';
            $postId = $post->ID;

            $data['load_exceptions'] = $this->getLoadExceptions($type, $postId);

            if (is_singular()) {
                // Current Post Type
                $data['post_type'] = $post->post_type;

                // Are there any assets unloaded for this specific post type?
                // (e.g. page, post, product (from WooCommerce) or other custom post type)
                $data['post_type_unloaded'] = $this->getPostTypeUnload($data['post_type']);
            }

            $this->parseTemplate('settings-frontend', $data, true);
        } elseif ($isDashboardView) {
            // AJAX call from the WordPress Dashboard
            echo self::STARTDEL
                .base64_encode(json_encode($list)).
                self::ENDDEL;
        }
    }

    /**
     * @param $name
     * @param array $data
     * @param bool|false $echo
     * @return bool|string
     */
    public function parseTemplate($name, $data = array(), $echo = false)
    {
        define('WPACU_TPL_LOADED', true);

        $templateFile = dirname(__DIR__) . '/templates/' . $name . '.php';

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
        $contents = isset($_POST['contents']) ? $_POST['contents'] : '';
        $postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : '';
        $postUrl = isset($_POST['post_url']) ? $_POST['post_url'] : '';

        $json = base64_decode(
            Misc::extractBetween(
                $contents,
                self::STARTDEL,
                self::ENDDEL
            )
        );

        $data = array();

        $data['all'] = (array)json_decode($json);
        $data['contents'] = $contents;

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

        $data['fetch_url'] = $postUrl;
        $data['global_unload'] = $this->getGlobalUnload();

        // Post Information
        $postData = get_post($postId);

        // Current Post Type
        $data['post_type'] = $postData->post_type;

        // Are there any assets unloaded for this specific post type?
        // (e.g. page, post, product (from WooCommerce) or other custom post type)
        $data['post_type_unloaded'] = $this->getPostTypeUnload($data['post_type']);

        //echo '<pre>'; print_r($data['post_type_unloaded']);

        $type = ($postId == 0) ? 'front_page' : 'post';

        $data['load_exceptions'] = $this->getLoadExceptions($type, $postId);

        $this->parseTemplate('meta-box-loaded', $data, true);

        exit;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function alterAssetObj($data)
    {
        $siteUrl = get_option('siteurl');

        if (! empty($data['all']['styles'])) {
            $data['core_styles_loaded'] = false;

            foreach ($data['all']['styles'] as $key => $obj) {
                if (! isset($obj->handle)) {
                    unset($data['all']['styles']['']);
                    continue;
                }

                if (isset($data['all']['styles'][$key])) {
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
                            $data['all']['styles'][$key]->wp = true;
                            $data['core_styles_loaded'] = true;
                        }

                        // Determine source href
                        if (substr($obj->src, 0, 1) == '/'
                            && substr($obj->src, 0, 2) != '//'
                        ) {
                            $obj->srcHref = $siteUrl . $obj->src;
                        } else {
                            $obj->srcHref = $obj->src;
                        }
                    }
                }
            }
        }

        if (! empty($data['all']['scripts'])) {
            $data['core_scripts_loaded'] = false;

            if (isset($data['contents'])) {
                $headPart = Misc::extractBetween($data['contents'], '<head', '</head>');
                $bodyPart = Misc::extractBetween($data['contents'], '<body', '</body>');
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
                        if (substr($obj->src, 0, 1) == '/'
                            && substr($obj->src, 0, 2) != '//'
                        ) {
                            $obj->srcHref = $siteUrl . $obj->src;
                        } else {
                            $obj->srcHref = $obj->src;
                        }
                    }

                    if (in_array($obj->handle, array('jquery'))) {
                        $data['all']['scripts'][$key]->wp = true;
                        $data['core_scripts_loaded'] = true;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param int $postId
     * @return array|mixed|string
     */
    public function getAssetsUnloaded($postId = 0)
    {
        // Post Type (Overwrites 'front' if we are in a singular post)
        global $post;
        $postId = (isset($post->ID) && is_singular()) ? $post->ID : $postId;

        if (! $this->assetsRemoved) {
            // For Home Page (latest blog posts)
            if ($postId < 1) {
                $this->assetsRemoved = get_option(WPACU_PLUGIN_NAME . '_front_page_no_load');
                return $this->assetsRemoved;
            } elseif ($postId > 0) {
                $this->assetsRemoved = get_post_meta($postId, '_' . WPACU_PLUGIN_NAME . '_no_load', true);
            }

            if ($this->assetsRemoved == '') {
                $this->assetsRemoved = json_encode(array('styles' => array(), 'scripts' => array()));
            }
        }

        return $this->assetsRemoved;
    }
}
