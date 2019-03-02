<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}

if ($data['get_assets']) {
    ?>
    <input type="hidden"
           id="wpacu_ajax_fetch_assets_list_dashboard_view"
           name="wpacu_ajax_fetch_assets_list_dashboard_view"
           value="1" />
<?php
}
?>
<div id="wpacu_meta_box_content">
    <?php
    if ($data['get_assets']) {
    ?>
        <img src="<?php echo admin_url(); ?>/images/spinner.gif" align="top" width="20" height="20" alt="" />&nbsp;
        <?php echo sprintf(__('Fetching the loaded scripts and styles for <strong>%s</strong> <br /><br /> Please wait... <br /><br /> In case the list does not show consider checking your internet connection and the actual page that is being fetched to see if it loads completely.', WPACU_PLUGIN_TEXT_DOMAIN), $data['fetch_url']); ?>
        <p style="margin-bottom: 0;"><?php echo sprintf(
                __('If you believe fetching the page takes too long and the assets should have loaded by now, I suggest you go to "Settings", make sure "Manage in front-end" is checked and then %smanage the assets in the front-end%s.', WPACU_PLUGIN_TEXT_DOMAIN),
                '<a href="'.$data['fetch_url'].'#wpacu_wrap_assets">',
                '</a>'
            ); ?></p>
        <?php
    } elseif ($data['status'] === 2) {
	    echo '<p>'.__('In order to manage the CSS/JS files here, you need to have "Manage in the Dashboard?" enabled within the plugin\'s settings ("General &amp; Files Management" tab).', WPACU_PLUGIN_TEXT_DOMAIN).'</p>';
	    echo '<p style="margin-bottom: 0;">'.__('If you prefer to manage the assets within the front-end view and wish to hide this meta box, you can click on "Screen Options" at the top of this page and deselect "Asset CleanUp: CSS &amp; JavaScript Manager".').'</p>';
    } elseif ($data['status'] === 3) {
        _e('The styles and scripts will be available for unload once this post/page is <strong>public</strong> and <strong>publish</strong>ed as the whole page needs to be scanned for all the loaded assets.', WPACU_PLUGIN_TEXT_DOMAIN);
        ?>
        <p class="wpacu-warning" style="margin: 15px 0 0; padding: 10px; font-size: inherit;"><span class="dashicons dashicons-image-rotate" style="-webkit-transform: rotateY(180deg); transform: rotateY(180deg);"></span> &nbsp;<?php _e('If this post/page was meanwhile published (after you saw the above notice), just reload this edit page and you should see the list of CSS/JS files loaded in the page.', WPACU_PLUGIN_TEXT_DOMAIN); ?></p>
    <?php
    } elseif ($data['status'] === 4) {
        ?>
            <p style="margin-bottom: 0;">
                <span class="dashicons dashicons-info"></span>
                <?php
                _e('There are no CSS/JS to manage as the permalink for this attachment redirects to the attachment itself because <em>"Redirect attachment URLs to the attachment itself?"</em> is set to <em>"Yes"</em> in <em>"Search Appearance - Yoast SEO" - "Media"</em> tab).', WPACU_PLUGIN_TEXT_DOMAIN);
                ?>
            </p>
        <?php
    }
    ?>
</div>