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

    <form method="post" action="options.php">
        <?php settings_fields('wpacu-plugin-settings-group'); ?>
        <?php do_settings_sections('wpacu-plugin-settings-group'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_frontend">Manage in the Front-end?</label>
                </th>
                <td><input id="wpacu_frontend" type="checkbox"
                    <?php echo (($data['frontend_show'] == 1) ? 'checked="checked"' : ''); ?>
                    name="<?php echo WPACU_PLUGIN_NAME.'_frontend_show'; ?>"
                    value="1" /> &nbsp; <small>If you are logged in, this will make the list of assets show below the page that you view (either home page, a post or a page).</small>
                    <p><small>The area will be shown through the <code>wp_footer</code> action so in case you do not see the asset list at the bottom of the page, make sure the theme is using <a href="https://codex.wordpress.org/Function_Reference/wp_footer"><code>wp_footer()</code></a> function before the <code>&lt;/body&gt;</code> tag. Any theme that follows the standards should have it. If not, you will have to add it to make sure other plugins and code from functions.php will work fine.</small></p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>