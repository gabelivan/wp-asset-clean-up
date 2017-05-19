<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}
?>
<div class="wrap">
    <h1><?php _e('WP Asset CleanUp', WPACU_PLUGIN_NAME); ?></h1>
    <h2><?php _e('Plugin Settings', WPACU_PLUGIN_NAME); ?></h2>

    <form method="post" action="">
        <input type="hidden" name="wpacu_settings_page" value="1" />
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_dashboard">Manage in the Dashboard?</label>
                </th>
                <td><input id="wpacu_dashboard" type="checkbox"
                        <?php echo (($data['dashboard_show'] == 1) ? 'checked="checked"' : ''); ?>
                           name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[dashboard_show]"
                           value="1" />&nbsp;<label for="wpacu_dashboard"><small>This will show the list of assets in a meta box on edit the post (any type) / page within the Dashboard</small></label>
                    <p><small>The assets would be retrieved via AJAX call(s) that will fetch the post/page URL and extract all the styles &amp; scripts that are enqueued.</small></p>
                    <p><small>Note that sometimes the assets list is not loading within the Dashboard. That could be because "mod_security" Apache module is enabled or some securiy plugins are blocking the AJAX request. If this option doesn't work, consider managing the list in the front-end view.</small></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_dom_get_type">Assets Retrieval Mode (if managed in the Dashboard)</label>
                </th>
                <td><select id="wpacu_dom_get_type" name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[dom_get_type]">
                        <option <?php if ($data['dom_get_type'] === 'direct') { ?>selected="selected"<?php } ?> value="direct">Direct</option>
                        <option <?php if ($data['dom_get_type'] === 'wp_remote_post') { ?>selected="selected"<?php } ?> value="wp_remote_post">WP Remote Post</option>
                    </select>
                    <ul>
                        <li style="margin-bottom: 20px;"><strong>Direct</strong> - <small>This one makes an AJAX call directly on the URL for which the assets are retrieved, then an extra WordPress AJAX call to process the list. Sometimes, due to some external factors (e.g. mod_security module from Apache, security plugin or the fact that non-http is forced for the front-end view and the AJAX request will be blocked), this might not work and another choice method might work better. This used to be the only option available, prior to version 1.2.4.4 and is set as default.</small></li>
                        <li><strong>WP Remote Post</strong> - <small>It makes a WordPress AJAX call and gets the HTML source code through wp_remote_post(). This one is less likely to be blocked as it is made on the same protocol (no HTTP request from HTTPS). However, in some cases (e.g. a different load balancer configuration), this might not work when the call to fetch a domain's URL (your website) is actually made from the same domain.</small></li>
                    </ul>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_frontend">Manage in the Front-end?</label>
                </th>
                <td><input id="wpacu_frontend" type="checkbox"
                    <?php echo (($data['frontend_show'] == 1) ? 'checked="checked"' : ''); ?>
                    name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[frontend_show]"
                           value="1" />&nbsp;<label for="wpacu_frontend"><small>If you are logged in, this will make the list of assets show below the page that you view (either home page, a post or a page).</small></label>
                    <p><small>The area will be shown through the <code>wp_footer</code> action so in case you do not see the asset list at the bottom of the page, make sure the theme is using <a href="https://codex.wordpress.org/Function_Reference/wp_footer"><code>wp_footer()</code></a> function before the <code>&lt;/body&gt;</code> tag. Any theme that follows the standards should have it. If not, you will have to add it to make sure other plugins and code from functions.php will work fine.</small></p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>