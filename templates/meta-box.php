<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}
?>
<div id="wpacu_meta_box_content">
    <?php
    if ($data['get_assets']) {
    ?>
        <img src="<?php echo admin_url(); ?>/images/spinner.gif" align="top" width="20" height="20" alt="" />&nbsp;
        <?php echo sprintf(__('Fetching the loaded scripts and styles for <strong>%s</strong> <br /><br /> Please wait... <br /><br /> In case the list does not show consider checking your internet connection and the actual page that is being fetched to see if it loads completely.', WPACU_PLUGIN_NAME), $data['fetch_url']); ?>
        <p><?php echo sprintf(
                __('If you believe fetching the page takes too long and the assets should have loaded by now, I suggest you go to "Settings", make sure "Manage in front-end" is checked and then %smanage the assets in the front-end%s.', WPACU_PLUGIN_NAME),
                '<a href="'.$data['fetch_url'].'#wpacu_wrap_assets">',
                '</a>'
            ); ?></p>
        <?php
    } else {
        _e('The styles and scripts will be available for unload once this post/page is <strong>public</strong> and <strong>publish</strong>ed as the whole page needs to be scanned for all the loaded assets.', WPACU_PLUGIN_NAME);
    }
    ?>
</div>