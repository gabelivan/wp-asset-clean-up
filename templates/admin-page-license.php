<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}

include_once '_top-area.php';
?>
<div class="wpacu-wrap">

    <p>You're using the lite version of <?php echo WPACU_PLUGIN_TITLE; ?> (v<?php echo WPACU_PLUGIN_VERSION; ?>), so no license key is needed. You'll receive automatic notifications whenever a new version is available for download.</p>
    <p><em>To unlock all features and get premium support, you can <a href="<?php echo WPACU_PLUGIN_GO_PRO_URL; ?>?utm_source=plugin_license">upgrade to the Pro version</a>.</em></p>

    <div class="wrap-upgrade-info">
        <p><span class="dashicons dashicons-info"></span> If you already purchased the Pro version and you don't know how to activate it, <a href="admin.php?page=wpassetcleanup_get_help">follow the steps from the "Help" section</a>.</p>
        <div class="wpacu-clearfix"></div>
    </div>
</div>