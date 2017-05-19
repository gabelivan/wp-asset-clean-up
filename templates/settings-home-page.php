<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}
?>
<h1><?php _e('WP Asset CleanUp', WPACU_PLUGIN_NAME); ?></h1>
<h2><?php _e('Home Page Scripts &amp; Styles Management', WPACU_PLUGIN_NAME); ?></h2>

<?php
if ($data['wpacu_settings']['dashboard_show'] != 1) {
    ?>
    <div class="error" style="padding: 10px;"><?php echo sprintf(__('As "Manage in the Dashboard?" is not enabled in "%sSettings%s", you can not manage the assets from the Dashboard.', WPACU_PLUGIN_NAME), '<a href="admin.php?page=wpassetcleanup_settings">', '</a>'); ?></div>
    <?php
    return;
}

if ($data['show_on_front'] === 'page') {
    ?>
    <p><?php _e('In "Settings" -&gt; "Reading" you have selected a static page for "Front page displays" setting. To manage the assets (.CSS &amp; .JS) that will NOT LOAD, use the link(s) below:', WPACU_PLUGIN_NAME); ?></p>
    <div>
        <ul>
            <?php
            if ($data['page_on_front']) {
            ?>
                <li>
                    <?php _e('Front page:', WPACU_PLUGIN_NAME); ?>
                    <a href="post.php?post=<?php echo $data['page_on_front']; ?>&action=edit#<?php echo WPACU_PLUGIN_NAME; ?>_asset_list"><strong><?php echo $data['page_on_front_title']; ?></strong></a>
                </li>
            <?php
            }

            if ($data['page_for_posts']) {
                ?>
                <li>
                    <?php _e('Posts page:', WPACU_PLUGIN_NAME); ?>
                    <a href="post.php?post=<?php echo $data['page_for_posts']; ?>&action=edit#<?php echo WPACU_PLUGIN_NAME; ?>_asset_list"><strong><?php echo $data['page_for_posts_title']; ?></strong></a>
                </li>
                <?php
            }
            ?>
        </ul>
    </div>
    <p><?php echo sprintf(__('To read more about creating a static front page in WordPress, %scheck the Codex%s.', WPACU_PLUGIN_NAME), '<a href="https://codex.wordpress.org/Creating_a_Static_Front_Page">', '</a>'); ?></p>
    <?php
} elseif ($data['show_on_front'] == 'posts') {
?>
    <form id="wpacu_home_page_form" method="post" action="">
        <p><?php echo sprintf(__('Your front (home) page URL is <strong>%s</strong>'), $data['site_url']); ?></p>

        <p><?php _e('Here you can manage the assets that are not loading for the home page. It is only applicable if "Front page displays" is set to "Your latest posts" (in "Settings" -&gt; "Reading"). When you edit a page/post (e.g. "Posts" -&gt; "All Posts", "Pages" -&gt; "All Pages" etc.), you will see the list inside a meta box.', WPACU_PLUGIN_NAME); ?></p>

        <p>The plugin uses <a target="_blank" href="https://codex.wordpress.org/Function_Reference/is_front_page">is_front_page()</a> and <a href="https://codex.wordpress.org/Conditional_Tags#The_Main_Page">is_home()</a> WordPress functions to check if the visitor is on the home page. Note that the setting will also apply to pages such as <code>/page/2</code> <code>page/3</code> etc. in case the latest blog posts are paginated.</p>

        <div id="wpacu_meta_box_content">
            <img src="<?php echo admin_url(); ?>/images/spinner.gif" align="top" width="20" height="20" alt="" />&nbsp;
            <?php _e('We\'re getting the loaded scripts and styles for the home page. Please wait...', WPACU_PLUGIN_NAME); ?>
        </div>

        <p><?php echo sprintf(
                __('If you believe fetching the page takes too long and the assets should have loaded by now, I suggest you go to "Settings", make sure "Manage in front-end" is checked and then %smanage the assets in the front-end%s.', WPACU_PLUGIN_NAME),
                '<a href="'.$data['site_url'].'#wpacu_wrap_assets">',
                '</a>'
            ); ?></p>

        <input type="hidden" name="<?php echo $data['nonce_name']; ?>" value="<?php echo $data['nonce_value']; ?>" />
        <p class="submit"><input type="submit" name="submit" id="submit" class="hidden button button-primary" value="<?php esc_attr_e('Update', WPACU_PLUGIN_NAME); ?>"></p>
    </form>
<?php
} else {
    ?>
    <p>It looks like in "Settings" -&gt; "Reading" (/wp-admin/options-reading.php), you have neither of the following options checked: "Your latest posts" and "A static page (select below)".</p>
    <p>Your theme or a plugin could interfere with it. Consider enabling "Manage in the Front-end?" in plugin's settings (WP Asset CleanUp -&gt; Settings). This should show the list of all assets at the bottom of your home page on front-end view (only if you're logged in).</p>
    <p>If you already tried the suggested option and still can't make it work, <a href="https://wordpress.org/support/plugin/wp-asset-clean-up">please open a ticket</a> on the plugin's support page.</p>
<?php
}
