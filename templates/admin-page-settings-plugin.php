<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}

include_once '_top-area.php';

// [wpacu_lite]
$availableForPro = '<a href="'.WPACU_PLUGIN_GO_PRO_URL.'?utm_source=plugin_settings" class="go-pro-link-no-style"><span class="tooltip">Available for Pro users<br />Click to unlock all features!</span> <img width="20" height="20" src="'.WPACU_PLUGIN_URL.'/assets/icons/icon-lock.svg" valign="top" alt="" /></a> &nbsp; ';
// [/wpacu_lite]
?>
<div class="wpacu-wrap">
    <h1><?php echo WPACU_PLUGIN_TITLE; ?></h1>

    <form method="post" action="">
        <input type="hidden" name="wpacu_settings_page" value="1" />
        <h2><?php _e('Plugin Usage Settings', WPACU_PLUGIN_NAME); ?></h2>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_dashboard">Manage in the Dashboard?</label>
                </th>
                <td>
                    <label class="wpacu_switch">
                    <input id="wpacu_dashboard"
                           type="checkbox"
                           <?php echo (($data['dashboard_show'] == 1) ? 'checked="checked"' : ''); ?>
                           name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[dashboard_show]"
                           value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <small>This will show the list of assets in a meta box on edit the post (any type) / page within the Dashboard</small>
                    <p><small>The assets would be retrieved via AJAX call(s) that will fetch the post/page URL and extract all the styles &amp; scripts that are enqueued.</small></p>
                    <p><small>Note that sometimes the assets list is not loading within the Dashboard. That could be because "mod_security" Apache module is enabled or some securiy plugins are blocking the AJAX request. If this option doesn't work, consider managing the list in the front-end view.</small></p>
                </td>
            </tr>
            <tr id="wpacu-settings-assets-retrieval-mode"
                <?php if (! ($data['dashboard_show'] == 1)) { echo 'style="display: none;"'; } ?>
                valign="top">
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
                <td>
                    <label class="wpacu_switch">
                        <input id="wpacu_frontend"
                               type="checkbox"
					        <?php echo (($data['frontend_show'] == 1) ? 'checked="checked"' : ''); ?>
                               name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[frontend_show]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <small>If you are logged in, this will make the list of assets show below the page that you view (either home page, a post or a page).</small>
                    <p><small>The area will be shown through the <code>wp_footer</code> action so in case you do not see the asset list at the bottom of the page, make sure the theme is using <a href="https://codex.wordpress.org/Function_Reference/wp_footer"><code>wp_footer()</code></a> function before the <code>&lt;/body&gt;</code> tag. Any theme that follows the standards should have it. If not, you will have to add it to make sure other plugins and code from functions.php will work fine.</small></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_assets_list_layout">Assets List Layout</label>
                </th>
                <td>
			        <?php echo $availableForPro; ?>
                    <label>
                        <select id="wpacu_assets_list_layout"
                                name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[assets_list_layout]">
                            <option value="default">All Styles &amp; All Scripts * 2 separate lists (default)</option>
                            <option disabled="disabled" value="all">All Styles &amp; Scripts * 1 mixed list sorted by name (Pro Version)</option>
                        </select>
                    </label>

                    <br />

                    <p><small>These are various ways in which the list of assets that you will manage will show up. Depending on your preference, you might want to see the list of styles &amp; scripts first, or all together sorted in alphabetical order etc.</small></p>
                    <p><small>Options that are disabled are available in the Pro version.</small></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_assets_list_layout">On Assets List Layout Load, keep "Styles &amp; Scripts" area:</label>
                </th>
                <td>
                    <ul class="assets_list_layout_areas_status_choices">
                        <li>
                            <label for="assets_list_layout_areas_status_expanded">
                                <input id="assets_list_layout_areas_status_expanded"
                                       <?php if (! $data['assets_list_layout_areas_status'] || $data['assets_list_layout_areas_status'] === 'expanded') { ?>checked="checked"<?php } ?>
                                       type="radio"
                                       name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[assets_list_layout_areas_status]"
                                       value="expanded"> Expanded (Default)
                            </label>
                        </li>
                        <li>
                            <label for="assets_list_layout_areas_status_contracted">
                                <input id="assets_list_layout_areas_status_contracted"
                                       <?php if ($data['assets_list_layout_areas_status'] === 'contracted') { ?>checked="checked"<?php } ?>
                                       type="radio"
                                       name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[assets_list_layout_areas_status]"
                                       value="contracted"> Contracted
                            </label>
                        </li>
                    </ul>
                    <div class="wpacu-clearfix"></div>

                    <p><small>Sometimes, when you have plenty of elements in the edit page, you might want to contract the list of assets when you're viewing the page as it will save space. This can be a good practice, especially when you finished optimising the pages and you don't want to keep seeing the long list of files everytime you edit a page.</small></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" class="setting_title">
                    <label for="wpacu_frontend">Enable Test Mode?</label>
                    <p class="wpacu_subtitle"><small><em>Apply plugin's changes for the admin only</em></small></p>
                    <p class="wpacu_read_more"><a target="_blank" href="https://assetcleanup.com/docs/?p=84">Read More</a></p>
                </th>
                <td>
                    <label class="wpacu_switch">
                        <input id="wpacu_frontend"
                               type="checkbox"
					        <?php echo (($data['test_mode'] == 1) ? 'checked="checked"' : ''); ?>
                               name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[test_mode]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <small>This is great for debugging when you're going through trial and error while removing unneeded CSS &amp; JavaScript on your website. Your visitors will load the website with all the settings &amp; assets loaded (just like it was before you activated the plugin).</small>
                    <p><small>For instance, you have an eCommerce website (e.g. WooCommerce, Easy Digital Downloads), and you're worried that unloading one wrong asset could break the "add to cart" functionality or the layout of the product page. You can enable this option, do the unloading for the CSS &amp; JavaScript files you believe are not needed on certain pages, test to check if everything is alright, and then disable test mode to enable the unloading for your visitors too (not only the admin).</small></p>
                </td>
            </tr>
        </table>

        <hr />

        <h2><?php _e('Site-Wide Unload For Common WordPress Core CSS &amp; JS Files', WPACU_PLUGIN_NAME); ?></h2>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_disable_emojis">Disable Emojis Site-Wide?</label>
                </th>
                <td>
                    <label class="wpacu_switch">
                        <input id="wpacu_disable_emojis"
                               type="checkbox"
                               <?php echo (($data['disable_emojis'] == 1) ? 'checked="checked"' : ''); ?>
                               name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[disable_emojis]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <small>This will unload WordPress' Emojis (the smiley icons)</small>
                    <p><small>As of WordPress 4.2, a new feature was introduced that allows you to use the new Emojis. While on some WordPress setups is useful, in many situations (especially when you are not using WordPress as a blog), you just don’t need them and the file /wp-includes/js/wp-emoji-release.min.js is loaded along with extra inline JavaScript code which add up to the number of loaded HTTP requests.</small></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_disable_jquery_migrate">Disable jQuery Migrate Site-Wide?</label>
                </th>
                <td>
                    <label class="wpacu_switch">
                        <input id="wpacu_disable_jquery_migrate" type="checkbox"
					        <?php echo (($data['disable_jquery_migrate'] == 1) ? 'checked="checked"' : ''); ?>
                               name="<?php echo WPACU_PLUGIN_NAME.'_global_unloads'; ?>[disable_jquery_migrate]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <small>This will unload jQuery Migrate (<em>jquery-migrate(.min).js</em>)</small>
                    <p><small>This is a JavaScript library that allows older jQuery code (up to version jQuery 1.9) to run on the latest version of jQuery avoiding incompatibility problems. Unless your website is using an old theme or has a jQuery plugin that was written a long time ago, this file is likely not needed to load. Consider disabling it to improve page loading time. Make sure to properly test the website.</small></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_disable_comment_reply">Disable Comment Reply Site-Wide?</label>
                </th>
                <td>
                    <label class="wpacu_switch">
                        <input id="wpacu_disable_comment_reply" type="checkbox"
					        <?php echo (($data['disable_comment_reply'] == 1) ? 'checked="checked"' : ''); ?>
                               name="<?php echo WPACU_PLUGIN_NAME.'_global_unloads'; ?>[disable_comment_reply]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <small>This will unload Comment Reply (<em>/wp-includes/js/comment-reply(.min).js</em>)</small>
                    <p><small>This is safe to unload if you're not using WordPress as a blog, do not want visitors to leave comments or you've replaced the default WordPress comments with a comment platform such as Disqus or Facebook.</small></p>
                </td>
            </tr>
        </table>

        <hr />
        <p><em><strong>Note:</strong> The options that have a lock are available to Pro users. <a href="<?php echo WPACU_PLUGIN_GO_PRO_URL; ?>?utm_source=plugin_settings">Click here to upgrade!</a></em></p>
        <hr />

        <h2><?php _e('Page Speed Score Booster: Extras', WPACU_PLUGIN_NAME); ?></h2>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_remove_query_strings">Remove Query Strings from CSS &amp; JS?</label>
                </th>
                <td>
                    <?php echo $availableForPro; ?>
                    <label class="wpacu_switch wpacu_locked_for_pro">
                        <input id="wpacu_remove_query_strings" type="checkbox"
                               disabled="disabled"
                               name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[remove_query_strings]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <small>This will remove everything that is appended after question mark (?) from the source of the CSS and JavaScript files * e.g. <em>/wp-plugins/custom-plugin-here/style.min.css<strong>?ver=1.4.2</strong></em> to <em>/wp-plugins/custom-plugin-here/style.min.css</em></small>
                    <p><small>Sometimes, query strings can cause caching issues (they are not cached by some proxy caching servers), thus, they can be removed. <a target="_blank" href="https://gtmetrix.com/remove-query-strings-from-static-resources.html">Read more</a>.</small></p>
                </td>
            </tr>
        </table>

        <hr />

        <h2><?php _e('Cleanup unused elements within HEAD section', WPACU_PLUGIN_NAME); ?></h2>
        <p>There are elements that are enabled by default in many WordPress environments, but not necessary to be enabled. Cleanup the unnecessary code between <code>&lt;head&gt;</code> and <code>&lt;/head&gt;</code>.</p>

        <table class="form-table">
            <!-- Remove "Really Simple Discovery (RSD)" link? -->
            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_remove_rsd_link">Remove "Really Simple Discovery (RSD)" link tag?</label>
                </th>
                <td>
	                <?php echo $availableForPro; ?>
                    <label class="wpacu_switch wpacu_locked_for_pro">
                        <input id="wpacu_remove_rsd_link"
                               type="checkbox"
                               disabled="disabled"
                               name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[remove_rsd_link]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <code>&lt;link rel=&quot;EditURI&quot; type=&quot;application/rsd xml&quot; title=&quot;RSD&quot; href=&quot;http://yourwebsite.com/xmlrpc.php?rsd&quot; /&gt;</code>
                    <p>XML-RPC clients use this discover method. If you do not know what this is and don't use service integrations such as <a href="http://www.flickr.com/services/api/request.xmlrpc.html" target="_blank">Flickr</a> on your WordPress website, you can remove it.</p>
                </td>
            </tr>

            <!-- Remove "Windows Live Writer" link? -->
            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_remove_wlw_link">Remove "Windows Live Writer" link tag?</label>
                </th>
                <td>
	                <?php echo $availableForPro; ?>
                    <label class="wpacu_switch wpacu_locked_for_pro">
                        <input id="wpacu_remove_wlw_link"
                               type="checkbox"
                               disabled="disabled"
                               name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[remove_wlw_link]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <code>&lt;link rel=&quot;wlwmanifest&quot; type=&quot;application/wlwmanifest xml&quot; href=&quot;https://yourwebsite.com/asset-optimizer/wp-includes/wlwmanifest.xml&quot; /&gt;</code>
                    <p>If you do not use Windows Live Writer to edit your blog contents, then it's safe to remove this.</p>
                </td>
            </tr>

            <!-- Remove "REST API" link tag? -->
            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_remove_rest_api_link">Remove "REST API" link tag?</label>
                </th>
                <td>
	                <?php echo $availableForPro; ?>
                    <label class="wpacu_switch wpacu_locked_for_pro">
                        <input id="wpacu_remove_rest_api_link"
                               type="checkbox"
                               disabled="disabled"
                               name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[remove_rest_api_link]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <code>&lt;link rel=&#39;https://api.w.org/&#39; href=&#39;https://yourwebsite.com/wp-json/&#39; /&gt;</code>
                    <p>Are you accessing your content through endpoints (e.g. https://yourwebsite.com/wp-json/, https://yourwebsite.com/wp-json/wp/v2/posts/1 - <em>1</em> in this example is the POST ID)? If not, you can remove this.</p>
                </td>
            </tr>

            <!-- Remove Pages/Posts "Shortlink" tag? -->
            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_remove_shortlink">Remove Pages/Posts "Shortlink" tag?</label>
                </th>
                <td>
	                <?php echo $availableForPro; ?>
                    <label class="wpacu_switch wpacu_locked_for_pro">
                        <input id="wpacu_remove_shortlink"
                               type="checkbox"
                               disabled="disabled"
                               name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[remove_shortlink]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <code>&lt;link rel=&#39;shortlink&#39; href=&quot;https://yourdomain.com/?p=1&quot;&gt;</code>
                    <p>Are you using SEO friendly URLs and do not need the default WordPress shortlink? You can just remove this as it bulks out the head section of your website.</p>
                </td>
            </tr>

            <!-- Remove "Post's Relational Links" tag? -->
            <tr valign="top">
                <th scope="row">
                    <label for="remove_posts_rel_links">Remove "Post's Relational Links" tag?</label>
                </th>
                <td>
	                <?php echo $availableForPro; ?>
                    <label class="wpacu_switch wpacu_locked_for_pro">
                        <input id="wpacu_remove_posts_rel_links"
                               type="checkbox"
                               disabled="disabled"
                               name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[remove_posts_rel_links]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <code>&lt;link rel=&#39;prev&#39; title=&#39;Title of adjacent post&#39; href=&#39;https://yourdomain.com/adjacent-post-slug-here/&#39; /&gt;</code>
                    <p>This removes relational links for the posts adjacent to the current post for single post pages.</p>
                </td>
            </tr>

            <!-- Remove "WordPress version" meta tag? -->
            <tr valign="top">
                <th scope="row">
                    <label for="remove_wp_version">Remove "WordPress version" meta tag?</label>
                </th>
                <td>
	                <?php echo $availableForPro; ?>
                    <label class="wpacu_switch wpacu_locked_for_pro">
                        <input id="wpacu_remove_wp_version"
                               type="checkbox"
                               disabled="disabled"
                               name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[remove_wp_version]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <code>&lt;meta name=&quot;generator&quot; content=&quot;WordPress 4.9.8&quot; /&gt;</code>
                    <p>This is good for security purposes as well, since it hides the WordPress version you're using (in case of hacking attempts).</p>
                </td>
            </tr>

            <!-- Remove "WordPress version" meta tag and all other tags? -->
            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_remove_generator_tag">Remove All "generator" meta tags?</label>
                </th>
                <td>
	                <?php echo $availableForPro; ?>
                    <label class="wpacu_switch wpacu_locked_for_pro">
                        <input id="wpacu_remove_generator_tag"
                               type="checkbox"
                               disabled="disabled"
                               name="<?php echo WPACU_PLUGIN_NAME.'_settings'; ?>[remove_generator_tag]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <code>e.g. &lt;meta name=&quot;generator&quot; content=&quot;Easy Digital Downloads v2.9.8&quot; /&gt;</code>
                    <p>This will remove all meta tags with the "generator" name, including the WordPress version. You could use a plugin or a theme that has added a generator notice, but you do not need to have it there. Moreover, it will hide the version of the plugins and theme you're using which is good for security reasons.</p>
                </td>
            </tr>

            <!-- Disable "XML-RPC" protocol support? -->
            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_disable_xmlrpc">Disable "XML-RPC" protocol support?</label>
                </th>
                <td>
	                <?php echo $availableForPro; ?>
                    <code>&lt;link rel=&quot;pingback&quot; href=&quot;https://www.yourwebsite.com/xmlrpc.php&quot; /&gt;</code>
                    <p style="margin-bottom: 10px;">This will disable XML-RPC protocol support (partially or completely) and cleans up the "pingback" tag from the HEAD section of your website.</p>
                    <p style="margin-bottom: 10px;">This is an API service used by WordPress for 3rd party applications, such as mobile apps, communication between blogs, plugins such as Jetpack. If you use, or are planning to use a remote system to post content to your website, you can keep this feature enabled (which it is by default). Many users do not use this function at all and if you're one of them, you can disable it.</p>

                    <p style="margin-bottom: 10px;"><strong>Disable XML-RPC Pingback Only</strong>: If you need the XML-RPC protocol support, but you do not use the pingbacks which are used by your website to notify another website that you have linked to it from your page(s), you can just disable the pinbacks and keep the other XML-RPC functionality. This is also a security measure to prevent DDoS attacks.</p>

                    <p style="margin-bottom: 10px;"><strong>Disable XML-RPC Completely</strong>: Id you do not use Jetpack plugin for off-site server communication or you only use the Dashboard to post content (without any remote software connection to the WordPress website such as Windows Live Writer or mobile apps), then you can disable the XML-RPC functionality. You can always re-enable it whenever you believe you'll need it.</p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>