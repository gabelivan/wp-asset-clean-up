<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}
?>

<form action="#wpacu_wrap_assets" method="post">
    <div id="wpacu_wrap_assets">
        <?php
        if ($data['is_updateable']) {
	        if (defined('WPACU_PAGE_JUST_UPDATED')) {
		        $updateClass = new \WpAssetCleanUp\Update;
		        ?>
                <div class="wpacu-updated-frontend"><em>
				        <?php if (\WpAssetCleanUp\Misc::isHomePage()) {
					        echo $updateClass->updateDoneMsg['homepage'];
				        } else {
					        echo $updateClass->updateDoneMsg['page'];
				        } ?>
                    </em></div>
		        <?php
	        }

            $wpacuMisc = new \WpAssetCleanUp\Misc();
            $activeCachePlugins = $wpacuMisc->getActiveCachePlugins();

            if (in_array('wp-rocket/wp-rocket.php', $activeCachePlugins)) {
                // Get WP Rocket Settings
                $wpRocketSettings = get_option('wp_rocket_settings');

                if ($wpRocketSettings['cache_logged_user'] == 1) {
	                ?>
                    <div class="wpacu-warning">
                        <strong><span class="dashicons dashicons-warning"></span> Important:</strong> You have enabled "<em>Enable caching for logged-in WordPress users</em>" in WP Rocket's Cache area. This could cause some issues with Asset CleanUp retrieving an outdated (cached) asset list below. If you experience issues such as unsaved settings or viewing assets from plugins that are disabled, consider using Asset CleanUp only in the Dashboard area (option "Manage in the Dashboard?" has to be enabled in plugin's settings).
                    </div>
                    <div class="clearfix"></div>
	                <?php
                }
            }
        ?>
            <p><small>* this area is shown only for the admin users and if "Manage in the Front-end?" was selected in the plugin's settings</small></p>
            <p><small>* 'admin-bar' and 'wpassetcleanup-style' are not included as they are irrelevant since they are used by the plugin for this area</small></p>
            <?php
            if ($data['is_woo_shop_page']) {
                ?>
                <p><strong><span style="color: #0f6cab;" class="dashicons dashicons-cart"></span> This a WooCommerce shop page ('product' type archive). Unloading assets will also take effect for the pagination/sorting pages (e.g. /2, /3, /?orderby=popularity etc.).</strong></p>
                <?php
            }

            if (isset($data['vars']['woo_url_not_match'])) {
                ?>
                <div class="wpacu_note wpacu_warning">
                    <p>Although this page is detected as the home page, its URL is not the same as the one from "General Settings" &#187; "Site Address (URL)" and the WooCommerce plugin is not active anymore. This could be the "Shop" page that is no longer active.</p>
                </div>
                <?php
            }

	        // [wpacu_pro]
	        do_action('wpacu_pro_frontend_before_asset_list');
	        // [/wpacu_pro]

            require_once 'meta-box-loaded.php';
        } else {
	        // Category, Tag, Search, 404, Author, Date pages (not supported by Lite version)
            $contentUnlockFeature = ' <p class="pro-page-unlock-notice">To unlock this feature, you can upgrade to the Pro version.</p>';
	        $utm_medium = '';

            if (\WpAssetCleanUp\Main::isWpDefaultSearchPage()) {
                echo '<span class="dashicons dashicons-search"></span> This is a <strong>WordPress Search Page</strong> and unloading the unneeded CSS &amp; JS can be done in Asset CleanUp Pro.'.$contentUnlockFeature;
	            $utm_medium = 'search_page';
            } elseif (is_404()) {
                echo '<span class="dashicons dashicons-warning"></span> This is a <strong>404 (Not Found) Page</strong> and unloading the unneeded CSS &amp; JS can be done in Asset CleanUp Pro.'.$contentUnlockFeature;
	            $utm_medium = '404_not_found_page';
            } elseif (is_author()) {
	            echo '<span class="dashicons dashicons-admin-users"></span> This is an <strong>Author Page</strong> and unloading the unneeded CSS &amp; JS can be done in Asset CleanUp Pro.'.$contentUnlockFeature;
	            $utm_medium = 'author_page';
            } elseif (is_category()) {
	            echo '<span class="dashicons dashicons-category"></span> This is a <strong>Category (Taxonomy) Page</strong> and unloading the unneeded CSS &amp; JS can be done in Asset CleanUp Pro.'.$contentUnlockFeature;
	            $utm_medium = 'category_page';
            } elseif (function_exists('is_product_category') && is_product_category()) {
	            echo '<img src="'.WPACU_PLUGIN_URL . '/assets/icons/woocommerce-icon-logo.svg'.'" alt="" style="height: 40px !important; margin-top: -6px; margin-right: 5px;" align="middle" /> This is a <strong>WooCommerce Product Category (Taxonomy) Page</strong> and unloading the unneeded CSS &amp; JS can be done in Asset CleanUp Pro. '.$contentUnlockFeature;
	            $utm_medium = 'woo_product_category_page';
            } elseif (is_date()) {
	            echo '<span class="dashicons dashicons-calendar-alt"></span> This is a <strong>Date (Archive) Page</strong> and unloading the unneeded CSS &amp; JS can be done in Asset CleanUp Pro.'.$contentUnlockFeature;
	            $utm_medium = 'date_page';
            } elseif (is_tag()) {
	            echo '<span class="dashicons dashicons-tag"></span> This is a <strong>Tag (Archive) Page</strong> and unloading the unneeded CSS &amp; JS can be done in Asset CleanUp Pro.'.$contentUnlockFeature;
	            $utm_medium = 'tag_page';
            } elseif (is_tax()) {
	            echo '<span class="dashicons dashicons-tag"></span> This is a <strong>Taxonomy Page</strong> and unloading the unneeded CSS &amp; JS can be done in Asset CleanUp Pro.'.$contentUnlockFeature;
	            $utm_medium = 'taxonomy_page';
            }
        ?>
            <p>
                <a class="go-pro-button" target="_blank" href="<?php echo WPACU_PLUGIN_GO_PRO_URL; ?>?utm_source=front_end_manage&utm_medium=<?php echo $utm_medium; ?>">
                    <span class="dashicons dashicons-cart"></span>&nbsp; Upgrade to Asset CleanUp Pro</a>
            </p>
        <?php
        }

        if ($data['is_updateable']) {
        ?>
        <div style="margin: 10px 0;">
            <button class="wpacu_update_btn"
                    type="submit"
                    name="submit"><span class="dashicons dashicons-update"></span> <?php esc_attr_e('UPDATE', WPACU_PLUGIN_NAME); ?></button>
        </div>

        <p align="right"><small>Powered by <?php echo WPACU_PLUGIN_TITLE; ?> (lite version), version <?php echo WPACU_PLUGIN_VERSION; ?></small></p>
        <?php } ?>
    </div>

    <?php
    if ($data['is_updateable']) {
    ?>
    <?php wp_nonce_field($data['nonce_action'], $data['nonce_name']); ?>
    <input type="hidden" name="wpacu_update_asset_frontend" value="1" />
    <?php } ?>
</form>