<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
	exit;
}

$tabIdArea = 'wpacu-setting-common-files-unload';
$styleTabContent = ($selectedTabArea === $tabIdArea) ? 'style="display: table-cell;"' : '';
?>
<div id="<?php echo $tabIdArea; ?>" class="wpacu-settings-tab-content" <?php echo $styleTabContent; ?>>
    <h2><?php _e('Site-Wide Unload For Common CSS &amp; JS Files', WPACU_PLUGIN_TEXT_DOMAIN); ?></h2>
    <p>This area allows you to quickly add the rule "Unload Site-wide" for the scripts below, which are often used in WordPress environments.</p>
    <table class="wpacu-form-table">
        <tr valign="top">
            <th scope="row">
                <label for="wpacu_disable_emojis">Disable Emojis Site-Wide?</label>
            </th>
            <td>
                <label class="wpacu_switch">
                    <input id="wpacu_disable_emojis"
                           type="checkbox"
						<?php echo (($data['disable_emojis'] == 1) ? 'checked="checked"' : ''); ?>
                           name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[disable_emojis]"
                           value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                &nbsp;
                This will unload WordPress' Emojis (the smiley icons)
                <p style="margin-top: 10px;">As of WordPress 4.2, a new feature was introduced that allows you to use the new Emojis. While on some WordPress setups is useful, in many situations (especially when you are not using WordPress as a blog), you just don’t need them and the file /wp-includes/js/wp-emoji-release.min.js is loaded along with extra inline JavaScript code which add up to the number of loaded HTTP requests.</p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="wpacu_disable_jquery_migrate">Disable jQuery Migrate Site-Wide? <span style="color: #cc0000;" class="dashicons dashicons-warning wordpress-core-file"><span class="wpacu-tooltip">WordPress Core File<br />Not sure if needed or not? In this case, it's better to leave it loaded to avoid breaking the website.</span></span></label>
            </th>
            <td>
                <label class="wpacu_switch">
                    <input id="wpacu_disable_jquery_migrate" type="checkbox"
						<?php echo (($data['disable_jquery_migrate'] == 1) ? 'checked="checked"' : ''); ?>
                           name="<?php echo WPACU_PLUGIN_ID . '_global_unloads'; ?>[disable_jquery_migrate]"
                           value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                &nbsp;
                This will unload jQuery Migrate (<em>jquery-migrate(.min).js</em>)
                <p style="margin-top: 10px;">This is a JavaScript library that allows older jQuery code (up to version jQuery 1.9) to run on the latest version of jQuery avoiding incompatibility problems. Unless your website is using an old theme or has a jQuery plugin that was written a long time ago, this file is likely not needed to load. Consider disabling it to improve page loading time. Make sure to properly test the website.</p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="wpacu_disable_comment_reply">Disable Comment Reply Site-Wide? <span style="color: #cc0000;" class="dashicons dashicons-warning wordpress-core-file"><span class="wpacu-tooltip">WordPress Core File<br />Not sure if needed or not? In this case, it's better to leave it loaded to avoid breaking the website.</span></span></label>
            </th>
            <td>
                <label class="wpacu_switch">
                    <input id="wpacu_disable_comment_reply" type="checkbox"
						<?php echo (($data['disable_comment_reply'] == 1) ? 'checked="checked"' : ''); ?>
                           name="<?php echo WPACU_PLUGIN_ID . '_global_unloads'; ?>[disable_comment_reply]"
                           value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                &nbsp;
                This will unload Comment Reply (<em>/wp-includes/js/comment-reply(.min).js</em>)
                <p style="margin-top: 10px;">This is safe to unload if you're not using WordPress as a blog, do not want visitors to leave comments or you've replaced the default WordPress comments with a comment platform such as Disqus or Facebook.</p>
            </td>
        </tr>
    </table>
</div>