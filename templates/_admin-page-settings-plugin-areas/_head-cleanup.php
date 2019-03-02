<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
	exit;
}

$tabIdArea = 'wpacu-setting-head-cleanup';
$styleTabContent = ($selectedTabArea === $tabIdArea) ? 'style="display: table-cell;"' : '';
?>
<div id="<?php echo $tabIdArea; ?>" class="wpacu-settings-tab-content" <?php echo $styleTabContent; ?>>
    <h2><?php _e('Remove unused elements from the &lthead&gt; section', WPACU_PLUGIN_TEXT_DOMAIN); ?></h2>

    <p>There are elements that are enabled by default in many WordPress environments, but not necessary to be enabled. Cleanup the unnecessary code between <code>&lt;head&gt;</code> and <code>&lt;/head&gt;</code>.</p>
    <table class="wpacu-form-table">
        <!-- Remove "Really Simple Discovery (RSD)" link? -->
        <tr valign="top">
            <th scope="row">
                <label for="wpacu_remove_rsd_link">Remove "Really Simple Discovery (RSD)" link tag?</label>
            </th>
            <td>
                <label class="wpacu_switch">
                    <input id="wpacu_remove_rsd_link" type="checkbox"
						<?php echo (($data['remove_rsd_link'] == 1) ? 'checked="checked"' : ''); ?>
                           name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[remove_rsd_link]"
                           value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                &nbsp;
                <code>&lt;link rel=&quot;EditURI&quot; type=&quot;application/rsd xml&quot; title=&quot;RSD&quot; href=&quot;http://yourwebsite.com/xmlrpc.php?rsd&quot; /&gt;</code>
                <p style="margin-top: 10px;">XML-RPC clients use this discover method. If you do not know what this is and don't use service integrations such as <a href="http://www.flickr.com/services/api/request.xmlrpc.html">Flickr</a> on your WordPress website, you can remove it.</p>
            </td>
        </tr>

        <!-- Remove "Windows Live Writer" link? -->
        <tr valign="top">
            <th scope="row">
                <label for="wpacu_remove_wlw_link">Remove "Windows Live Writer" link tag?</label>
            </th>
            <td>
                <label class="wpacu_switch">
                    <input id="wpacu_remove_wlw_link" type="checkbox"
						<?php echo (($data['remove_wlw_link'] == 1) ? 'checked="checked"' : ''); ?>
                           name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[remove_wlw_link]"
                           value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                &nbsp;
                <code>&lt;link rel=&quot;wlwmanifest&quot; type=&quot;application/wlwmanifest xml&quot; href=&quot;https://yourwebsite.com/wp-includes/wlwmanifest.xml&quot; /&gt;</code>
                <p style="margin-top: 10px;">If you do not use Windows Live Writer to edit your blog contents, then it's safe to remove this.</p>
            </td>
        </tr>

        <!-- Remove "REST API" link? -->
        <tr valign="top">
            <th scope="row">
                <label for="wpacu_remove_rest_api_link">Remove "REST API" link tag?</label>
            </th>
            <td>
                <label class="wpacu_switch">
                    <input id="wpacu_remove_rest_api_link" type="checkbox"
						<?php echo (($data['remove_rest_api_link'] == 1) ? 'checked="checked"' : ''); ?>
                           name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[remove_rest_api_link]"
                           value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                &nbsp;
                <code>&lt;link rel=&#39;https://api.w.org/&#39; href=&#39;https://yourwebsite.com/wp-json/&#39; /&gt;</code>
                <p style="margin-top: 10px;">Are you accessing your content through endpoints (e.g. https://yourwebsite.com/wp-json/, https://yourwebsite.com/wp-json/wp/v2/posts/1 - <em>1</em> in this example is the POST ID)? If not, you can remove this.</p>
            </td>
        </tr>

        <!-- Remove "Shortlink"? -->
        <tr valign="top">
            <th scope="row">
                <label for="wpacu_remove_shortlink">Remove Pages/Posts "Shortlink" tag?</label>
            </th>
            <td>
                <label class="wpacu_switch">
                    <input id="wpacu_remove_shortlink" type="checkbox"
						<?php echo (($data['remove_shortlink'] == 1) ? 'checked="checked"' : ''); ?>
                           name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[remove_shortlink]"
                           value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                &nbsp;
                <code>&lt;link rel=&#39;shortlink&#39; href=&quot;https://yourdomain.com/?p=1&quot;&gt;</code>
                <p style="margin-top: 10px;">Are you using SEO friendly URLs and do not need the default WordPress shortlink? You can just remove this as it bulks out the head section of your website.</p>
            </td>
        </tr>

        <!-- Remove "Post's Relational Links" tag? -->
        <tr valign="top">
            <th scope="row">
                <label for="wpacu_remove_posts_rel_links">Remove "Post's Relational Links" tag?</label>
            </th>
            <td>
                <label class="wpacu_switch">
                    <input id="wpacu_remove_posts_rel_links" type="checkbox"
						<?php echo (($data['remove_posts_rel_links'] == 1) ? 'checked="checked"' : ''); ?>
                           name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[remove_posts_rel_links]"
                           value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                &nbsp;
                <code>&lt;link rel=&#39;prev&#39; title=&#39;Title of adjacent post&#39; href=&#39;https://yourdomain.com/adjacent-post-slug-here/&#39; /&gt;</code>
                <p style="margin-top: 10px;">This removes relational links for the posts adjacent to the current post for single post pages.</p>
            </td>
        </tr>

        <!-- Remove "WordPress version" meta tag? -->
        <tr valign="top">
            <th scope="row">
                <label for="wpacu_remove_wp_version">Remove "WordPress version" meta tag?</label>
            </th>
            <td>
                <label class="wpacu_switch">
                    <input id="wpacu_remove_wp_version" type="checkbox"
						<?php echo (($data['remove_wp_version'] == 1) ? 'checked="checked"' : ''); ?>
                           name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[remove_wp_version]"
                           value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                &nbsp;
                <code>&lt;meta name=&quot;generator&quot; content=&quot;WordPress 4.9.8&quot; /&gt;</code>
                <p style="margin-top: 10px;">This is good for security purposes as well, since it hides the WordPress version you're using (in case of hacking attempts).</p>
            </td>
        </tr>

        <!-- Remove "WordPress version" meta tag and all other tags? -->
        <tr valign="top">
            <th scope="row">
                <label for="wpacu_remove_generator_tag">Remove All "generator" meta tags?</label>
            </th>
            <td>
                <label class="wpacu_switch">
                    <input id="wpacu_remove_generator_tag"
                           type="checkbox"
						<?php echo (($data['remove_generator_tag'] == 1) ? 'checked="checked"' : ''); ?>
                           name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[remove_generator_tag]"
                           value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                &nbsp;
                <code>e.g. &lt;meta name=&quot;generator&quot; content=&quot;Easy Digital Downloads v2.9.8&quot; /&gt;</code>
                <p style="margin-top: 10px;">This will remove all meta tags with the "generator" name, including the "WordPress version" meta tag. You could use a plugin or a theme that has added a generator notice, but you do not need to have it there. Moreover, it will hide the version of the plugins and theme you're using which is good for security reasons.</p>
            </td>
        </tr>

        <!-- Remove Main RSS Feed Link -->
        <tr valign="top">
            <th scope="row">
                <label for="wpacu_remove_main_feed_link">Remove Main RSS Feed Link?</label>
            </th>
            <td>
                <label class="wpacu_switch">
                    <input id="wpacu_remove_main_feed_link"
                           type="checkbox"
						<?php echo (($data['remove_main_feed_link'] == 1) ? 'checked="checked"' : ''); ?>
                           name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[remove_main_feed_link]"
                           value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                &nbsp;
                <code>&lt;link rel=&quot;alternate&quot; type=&quot;application/rss xml&quot; title=&quot;Your Site Title &amp;raquo; Feed&quot; href=&quot;https://www.yourwebsite.com/feed/&quot; /&gt;</code>
                <p style="margin-top: 10px;">If you do not use WordPress for blogging purposes at all, and it doesn't have any blog posts (apart from the main pages that you added), then you can remove the main feed link. It will also remove feeds for the following pages: categories, tags, custom taxonomies &amp; search results. Note that it will not remove comments RSS feeds which can be removed using the setting below. Some websites might have blog posts and would keep the main RSS feeds enabled, while removing the comments RSS feeds if they don't use the comments functionality.</p>
            </td>
        </tr>

        <!-- Remove Comment Feeds Link -->
        <tr valign="top">
            <th scope="row">
                <label for="wpacu_remove_comment_feed_link">Remove Comment RSS Feed Link?</label>
            </th>
            <td>
                <label class="wpacu_switch">
                    <input id="wpacu_remove_comment_feed_link"
                           type="checkbox"
						<?php echo (($data['remove_comment_feed_link'] == 1) ? 'checked="checked"' : ''); ?>
                           name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[remove_comment_feed_link]"
                           value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                &nbsp;
                <code>e.g. &lt;link rel=&quot;alternate&quot; type=&quot;application/rss xml&quot; title=&quot;Your Website Title &amp;raquo; Comments Feed&quot; href=&quot;https://www.yourdomain.com/comments/feed/&quot; /&gt;</code>
                <p style="margin-top: 10px;">If you do not use the comments functionality on your posts or do not use WordPress for blogging purposes at all, then you can remove the comments feed link.</p>
            </td>
        </tr>
    </table>
</div>
