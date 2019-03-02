<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}
?>
<div style="margin: 30px 0 0;" class="cleafix"></div>

    <h2><span class="dashicons dashicons-admin-home"></span> <?php _e('Home Page Scripts &amp; Styles Management', WPACU_PLUGIN_TEXT_DOMAIN); ?></h2>

<?php
do_action('wpacu_admin_notices');

if ($data['wpacu_settings']['dashboard_show'] != 1) {
	?>
    <div class="error" style="padding: 10px;"><?php echo sprintf(__('As "Manage in the Dashboard?" is not enabled in "%sSettings%s", you can not manage the assets from the Dashboard.', WPACU_PLUGIN_TEXT_DOMAIN), '<a href="admin.php?page=wpassetcleanup_settings">', '</a>'); ?></div>
	<?php
	return;
}

if ($data['show_on_front'] === 'page') {
	?>
    <p><?php _e('In "Settings" &#187; "Reading" you have selected a static page for "Front page displays" setting. To manage the assets (.CSS &amp; .JS) that will NOT LOAD, use the link(s) below:', WPACU_PLUGIN_TEXT_DOMAIN); ?></p>
    <div>
        <ul>
			<?php
			if ($data['page_on_front']) {
				?>
                <li>
					<?php _e('Front page:', WPACU_PLUGIN_TEXT_DOMAIN); ?>
                    <a href="<?php echo admin_url('post.php?post='.$data['page_on_front'].'&action=edit#'.WPACU_PLUGIN_ID.'_asset_list'); ?>"><strong><?php echo $data['page_on_front_title']; ?></strong></a>
                </li>
				<?php
			}

			if ($data['page_for_posts']) {
				?>
                <li>
					<?php _e('Posts page:', WPACU_PLUGIN_TEXT_DOMAIN); ?>
                    <a href="<?php echo admin_url('post.php?post='.$data['page_for_posts'].'&action=edit#'.WPACU_PLUGIN_ID.'_asset_list'); ?>"><strong><?php echo $data['page_for_posts_title']; ?></strong></a>
                </li>
				<?php
			}
			?>
        </ul>
    </div>
    <p><?php echo sprintf(__('To read more about creating a static front page in WordPress, %scheck the Codex%s.', WPACU_PLUGIN_TEXT_DOMAIN), '<a target="_blank" href="https://codex.wordpress.org/Creating_a_Static_Front_Page">', '</a>'); ?></p>
	<?php
} else {
	?>
    <form id="wpacu_home_page_form" method="post" action="">
        <input type="hidden" name="wpacu_manage_home_page_assets" value="1" />

        <input type="hidden"
               id="wpacu_ajax_fetch_assets_list_dashboard_view"
               name="wpacu_ajax_fetch_assets_list_dashboard_view"
               value="1" />

        <p><?php _e('Here you can unload files loaded on the home page. "Front page displays" (from "Settings" &#187; "Reading") is set to either "Your latest posts" (in "Settings" &#187; "Reading") OR a special layout (from a theme or plugin) was enabled.', WPACU_PLUGIN_TEXT_DOMAIN); ?> Changes will also apply to pages such as <code>/page/2</code> <code>page/3</code> etc. in case the latest blog posts are paginated.</p>

        <div id="wpacu_meta_box_content">
            <img src="<?php echo admin_url(); ?>/images/spinner.gif" align="top" width="20" height="20" alt="" />&nbsp;

			<?php _e('Retrieving the loaded scripts and styles for the home page. Please wait...', WPACU_PLUGIN_TEXT_DOMAIN); ?>

            <p><?php echo sprintf(
					__('If you believe fetching the page takes too long and the assets should have loaded by now, I suggest you go to "Settings", make sure "Manage in front-end" is checked and then %smanage the assets in the front-end%s.', WPACU_PLUGIN_TEXT_DOMAIN),
					'<a href="'.$data['site_url'].'#wpacu_wrap_assets">',
					'</a>'
				); ?></p>
        </div>

		<?php
		wp_nonce_field($data['nonce_name']);
		?>
        <div id="wpacu-update-button-area" class="no-left-margin">
            <p class="submit"><input type="submit" name="submit" id="submit" class="hidden button button-primary" value="<?php esc_attr_e('Update', WPACU_PLUGIN_TEXT_DOMAIN); ?>"></p>
        </div>
    </form>
	<?php
}