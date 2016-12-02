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
        <p><small>* this area is shown only for the admin users and if "Manage in the Front-end?" was selected in the plugin's settings</small></p>
        <p><small>* 'admin-bar', 'wpassetcleanup-icheck-square-red' and 'wpassetcleanup-style' are not included as they are irrelevant since they are used by the plugin for this area</small></p>
        <?php
        require_once 'meta-box-loaded.php';
        ?>
        <div style="margin: 10px 0;">
            <input class="wpacu_update_btn"
                   type="submit"
                   name="submit"
                   value="<?php esc_attr_e('UPDATE', WPACU_PLUGIN_NAME); ?>" />
        </div>

        <p align="right"><small>Powered by WP Asset CleanUp</small></p>
    </div>
    <?php wp_nonce_field($data['nonce_action'], $data['nonce_name']); ?>
    <input type="hidden" name="wpacu_update_asset_frontend" value="1" />
</form>