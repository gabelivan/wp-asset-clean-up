<?php
/*
 * No direct access to this file
 * This content is placed inside #wpacu_meta_box_content meta box DIV element
 */
if (! isset($data)) {
    exit;
}

$metaBoxLoadedFine = (! (isset($data['is_dashboard_view']) && $data['is_dashboard_view']
                && isset($data['wp_remote_post']) && !empty($data['wp_remote_post'])));

if (! $metaBoxLoadedFine) {
    // Errors for "WP Remove Post"? Print them out
    ?>
    <div class="ajax-wp-remote-post-call-error-area">
        <p><span class="dashicons dashicons-warning"></span> It looks like "WP Remote Post" method for retrieving assets via the Dashboard is not working in this environment.</p>
        <p>Since the server (from its IP) is making the call, it will not "behave" in the same way as the "Direct" method, which could bypass for instance any authentication request (you might use a staging website that is protected by login credentials).</p>
        <p>Consider using "Direct" method. If that doesn't work either, use the "Manage in the Front-end" option (which should always work in any instance) and submit a ticket regarding the problem you're having. Here's the output received by the call:</p>

        <table class="table-data">
            <tr>
                <td><strong>CODE:</strong></td>
                <td><?php echo $data['wp_remote_post']['response']['code']; ?></td>
            </tr>

            <tr>
                <td><strong>MESSAGE:</strong></td>
                <td><?php echo $data['wp_remote_post']['response']['message']; ?></td>
            </tr>

            <tr>
                <td><strong>OUTPUT:</strong></td>
                <td><?php echo $data['wp_remote_post']['body']; ?></td>
            </tr>
        </table>
    </div>
    <?php

    exit;
}

if (\WpAssetCleanUp\Misc::isHomePage()) {
    ?>
    <p><strong><span style="color: #0f6cab;" class="dashicons dashicons-admin-home"></span> You are currently viewing the home page.</strong></p>
	<?php
}

elseif (\WpAssetCleanUp\Misc::isBlogPage()) {
    ?>
    <p><strong><span style="color: #0f6cab;" class="dashicons dashicons-admin-post"></span> You are currently viewing the page that shows your latest posts.</strong></p>
	<?php
}

elseif ($data['bulk_unloaded_type'] === 'post_type') {
	$isWooPage = $iconShown = false;

	if (function_exists('is_woocommerce')
        && (is_woocommerce() || is_cart() || is_product_tag() || is_product_category() || is_checkout())
    ) {
        $isWooPage = true;
        $iconShown = WPACU_PLUGIN_URL . '/assets/icons/woocommerce-icon-logo.svg';
    }

    if (! $iconShown) {
	    switch ( $data['post_type'] ) {
		    case 'post':
			    $dashIconPart = 'post';
			    break;
		    case 'page':
			    $dashIconPart = 'page';
			    break;
		    case 'attachment':
			    $dashIconPart = 'media';
			    break;
		    default:
			    $dashIconPart = 'post';
	    }
    }
    ?>
    <p>
	<?php if ($isWooPage) { ?>
        <img src="<?php echo $iconShown; ?>" alt="" style="height: 40px !important; margin-top: -6px; margin-right: 5px;" align="middle" /> <strong>WooCommerce</strong>
    <?php } ?>
        <strong><?php if (! $iconShown) { ?><span style="color: #0f6cab;" class="dashicons dashicons-admin-<?php echo $dashIconPart; ?>"></span> <?php } ?> <u><?php echo $data['post_type']; ?></u> <?php if ($data['post_type'] !== 'post') {  echo 'post'; } ?> type.</strong></p>
    <?php
}

if (! is_404()) {
	?>
    <div class="wpacu_verified">
        <strong>Verified Page:</strong> <a target="_blank"
                                           href="<?php echo $data['fetch_url']; ?>"><span><?php echo $data['fetch_url']; ?></span></a>
    </div>
	<?php
}

if (isset($data['page_template'])) {
    ?>
    <div>
        <strong><?php if ($data['post_type'] === 'page') { echo 'Page'; } elseif ($data['post_type'] === 'post') { echo 'Post'; } ?>
            Template:</strong>
	    <?php
	    if (isset($data['all_page_templates'][$data['page_template']])) { ?>
            <u><?php echo $data['all_page_templates'][$data['page_template']]; ?></u>
	    <?php } ?>

        (<?php echo $data['page_template'];

	    if (isset($data['page_template_path'])) {
		    echo '&nbsp; &#10230; &nbsp;<em>'.$data['page_template_path'].'</em>';
	    }
	    ?>)
    </div>
<?php
}
?>
<div class="<?php if ($data['plugin_settings']['input_style'] !== 'standard') { ?>wpacu-switch-enhanced<?php } else { ?>wpacu-switch-standard<?php } ?>">
    <?php
	include_once __DIR__.'/meta-box-loaded-assets/view-default.php';
	?>
</div>
<?php

/*
 Bug Fix: Make sure that savePost() from Update class is triggered ONLY if the meta box is loaded
 Otherwise, an early form submit will erase any selected assets for unload by sending an empty $_POST[WPACU_PLUGIN_ID] request

 NOTE: In case no assets are retrieved, then it's likely that for some reason, fetching the assets from the Dashboard
 is not possible and the user will have to manage them in the front-end.
 We'll make sure that no existing assets (managed in the front-end) are removed when the user updates the post/page from the Dashboard
*/

// Check it again
if ($metaBoxLoadedFine) {
	$metaBoxLoadedFine = ( ! ( empty( $data['all']['styles'] ) && empty( $data['all']['scripts'] ) ) );
}

if ($metaBoxLoadedFine) {
    ?>
    <input type="hidden" name="wpacu_unload_assets_area_loaded" value="1" />
<?php } ?>
