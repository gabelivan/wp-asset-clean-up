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
        <h1>WP Asset CleanUp</h1>
        <?php
        if ($data['is_updateable']) {
        ?>
            <p><small>* this area is shown only for the admin users and if "Manage in the Front-end?" was selected in the plugin's settings</small></p>
            <p><small>* 'admin-bar', 'wpassetcleanup-icheck-square-red' and 'wpassetcleanup-style' are not included as they are irrelevant since they are used by the plugin for this area</small></p>
            <?php
            if ($data['is_woocommerce_shop_page']) {
                ?>
                <p><strong>This is the "Shop" page from WooCommerce plugin. Unloading assets will also take effect for the pagination/sorting pages (e.g. /2, /3, /?orderby=popularity etc.).</strong></p>
                <?php
            }

            if (isset($data['vars']['woo_url_not_match'])) {
                ?>
                <div class="wpacu_note wpacu_warning">
                    <p>Although this page is detected as the home page, its URL is not the same as the one from "General Settings" -&gt; "Site Address (URL)" and the WooCommerce plugin is not active anymore. This could be the "Shop" page that is no longer active.</p>
                </div>
                <?php
            }
            require_once 'meta-box-loaded.php';
        } else {
        ?>
            <p>This page format is not supported at this time (e.g. it is likely an archive having multiple posts, category or 404 page) and unloading assets for this type of page is not supported at the moment.</p>
            <p>If there are assets unloaded everywhere, then the rule will apply and they will not be loaded on this page.</p>
        <?php
        }

        if ($data['is_updateable']) {
        ?>
        <div style="margin: 10px 0;">
            <input class="wpacu_update_btn"
                   type="submit"
                   name="submit"
                   value="<?php esc_attr_e('UPDATE', WPACU_PLUGIN_NAME); ?>" />
        </div>

        <p align="right"><small>Powered by WP Asset CleanUp</small></p>
        <?php } ?>
    </div>

    <?php
    if ($data['is_updateable']) {
    ?>
    <?php wp_nonce_field($data['nonce_action'], $data['nonce_name']); ?>
    <input type="hidden" name="wpacu_update_asset_frontend" value="1" />
    <?php } ?>
</form>