<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}

include_once '_top-area.php';

// [wpacu_lite]
$availableForPro = '<a href="'.WPACU_PLUGIN_GO_PRO_URL.'?utm_source=plugin_settings" class="go-pro-link-no-style"><span class="wpacu-tooltip">Available for Pro users<br />Buy now to unlock all features!</span> <img width="20" height="20" src="'.WPACU_PLUGIN_URL.'/assets/icons/icon-lock.svg" valign="top" alt="" /></a> &nbsp; ';
// [/wpacu_lite]

do_action('wpacu_admin_notices');
?>
<div class="wpacu-wrap wpacu-settings-area <?php if ($data['input_style'] !== 'standard') { ?>wpacu-switch-enhanced<?php } else { ?>wpacu-switch-standard<?php } ?>">
    <form method="post" action="">
        <input type="hidden" name="wpacu_settings_page" value="1" />
        <h2><?php _e('Plugin Usage Settings', WPACU_PLUGIN_TEXT_DOMAIN); ?></h2>

        <table class="wpacu-form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_dashboard">Manage in the Dashboard?</label>
                </th>
                <td>
                    <label class="wpacu_switch">
                    <input id="wpacu_dashboard"
                           type="checkbox"
                           <?php echo (($data['dashboard_show'] == 1) ? 'checked="checked"' : ''); ?>
                           name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[dashboard_show]"
                           value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    This will show the list of assets in a meta box on edit the post (any type) / page within the Dashboard
                    <p>The assets would be retrieved via AJAX call(s) that will fetch the post/page URL and extract all the styles &amp; scripts that are enqueued.</p>
                    <p>Note that sometimes the assets list is not loading within the Dashboard. That could be because "mod_security" Apache module is enabled or some securiy plugins are blocking the AJAX request. If this option doesn't work, consider managing the list in the front-end view.</p>

                    <div id="wpacu-settings-assets-retrieval-mode"
                        <?php if (! ($data['dashboard_show'] == 1)) { echo 'style="display: none;"'; } ?>>

                            <ul id="wpacu-dom-get-type-selections">
                                <li>
                                    <label for="wpacu_dom_get_type">Select a retrieval way:</label>
                                </li>
                                <li>
                                    <label>
                                        <input class="wpacu-dom-get-type-selection"
                                               data-target="wpacu-dom-get-type-direct-info"
                                               <?php if ($data['dom_get_type'] === 'direct') { ?>checked="checked"<?php } ?>
                                               type="radio" name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[dom_get_type]"
                                               value="direct" /> Direct
                                    </label>
                                </li>
                                <li>
                                    <label>
                                        <input class="wpacu-dom-get-type-selection"
                                               data-target="wpacu-dom-get-type-wp-remote-post-info"
                                               <?php if ($data['dom_get_type'] === 'wp_remote_post') { ?>checked="checked"<?php } ?>
                                               type="radio" name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[dom_get_type]"
                                               value="wp_remote_post" /> WP Remote Post
                                    </label>
                                </li>
                            </ul>

                            <div class="wpacu-clearfix" style="height: 0;"></div>

                            <ul id="wpacu-dom-get-type-infos">
                                <li <?php if ($data['dom_get_type'] !== 'direct') { ?>style="display: none;"<?php } ?>
                                    class="wpacu-dom-get-type-info"
                                    id="wpacu-dom-get-type-direct-info">
                                    <strong>Direct</strong> - This one makes an AJAX call directly on the URL for which the assets are retrieved, then an extra WordPress AJAX call to process the list. Sometimes, due to some external factors (e.g. mod_security module from Apache, security plugin or the fact that non-http is forced for the front-end view and the AJAX request will be blocked), this might not work and another choice method might work better. This used to be the only option available, prior to version 1.2.4.4 and is set as default.
                                </li>
                                <li <?php if ($data['dom_get_type'] !== 'wp_remote_post') { ?>style="display: none;"<?php } ?>
                                    class="wpacu-dom-get-type-info"
                                    id="wpacu-dom-get-type-wp-remote-post-info">
                                    <strong>WP Remote Post</strong> - It makes a WordPress AJAX call and gets the HTML source code through wp_remote_post(). This one is less likely to be blocked as it is made on the same protocol (no HTTP request from HTTPS). However, in some cases (e.g. a different load balancer configuration), this might not work when the call to fetch a domain's URL (your website) is actually made from the same domain.
                                </li>
                            </ul>
                    </div>
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
                               name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[frontend_show]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    If you are logged in, this will make the list of assets show below the page that you view (either home page, a post or a page).
                    <p style="margin-top: 10px;">The area will be shown through the <code>wp_footer</code> action so in case you do not see the asset list at the bottom of the page, make sure the theme is using <a href="https://codex.wordpress.org/Function_Reference/wp_footer"><code>wp_footer()</code></a> function before the <code>&lt;/body&gt;</code> tag. Any theme that follows the standards should have it. If not, you will have to add it to make sure other plugins and code from functions.php will work fine.</p>
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
                                name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[assets_list_layout]">
                            <option value="default">All Styles &amp; All Scripts * 2 separate lists (default)</option>
                            <option disabled="disabled" value="all">All Styles &amp; Scripts * 1 mixed list sorted by name (Pro Version)</option>
                        </select>
                    </label>

                    <p style="margin-top: 10px;">These are various ways in which the list of assets that you will manage will show up. Depending on your preference, you might want to see the list of styles &amp; scripts first, or all together sorted in alphabetical order etc. Options that are disabled are available in the Pro version.</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label>On Assets List Layout Load, keep "Styles &amp; Scripts" area:</label>
                </th>
                <td>
                    <ul class="assets_list_layout_areas_status_choices">
                        <li>
                            <label for="assets_list_layout_areas_status_expanded">
                                <input id="assets_list_layout_areas_status_expanded"
                                       <?php if (! $data['assets_list_layout_areas_status'] || $data['assets_list_layout_areas_status'] === 'expanded') { ?>checked="checked"<?php } ?>
                                       type="radio"
                                       name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[assets_list_layout_areas_status]"
                                       value="expanded"> Expanded (Default)
                            </label>
                        </li>
                        <li>
                            <label for="assets_list_layout_areas_status_contracted">
                                <input id="assets_list_layout_areas_status_contracted"
                                       <?php if ($data['assets_list_layout_areas_status'] === 'contracted') { ?>checked="checked"<?php } ?>
                                       type="radio"
                                       name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[assets_list_layout_areas_status]"
                                       value="contracted"> Contracted
                            </label>
                        </li>
                    </ul>
                    <div class="wpacu-clearfix"></div>

                    <p>Sometimes, when you have plenty of elements in the edit page, you might want to contract the list of assets when you're viewing the page as it will save space. This can be a good practice, especially when you finished optimising the pages and you don't want to keep seeing the long list of files every time you edit a page.</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label>On Assets List Layout Load, keep "Inline code associated with this handle" area:</label>
                </th>
                <td>
                    <ul class="assets_list_inline_code_status_choices">
                        <li>
                            <label for="assets_list_inline_code_status_expanded">
                                <input id="assets_list_inline_code_status_expanded"
						               <?php if (! $data['assets_list_inline_code_status'] || $data['assets_list_inline_code_status'] === 'expanded') { ?>checked="checked"<?php } ?>
                                       type="radio"
                                       name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[assets_list_inline_code_status]"
                                       value="expanded"> Expanded (Default)
                            </label>
                        </li>
                        <li>
                            <label for="assets_list_inline_code_status_contracted">
                                <input id="assets_list_inline_code_status_contracted"
						               <?php if ($data['assets_list_inline_code_status'] === 'contracted') { ?>checked="checked"<?php } ?>
                                       type="radio"
                                       name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[assets_list_inline_code_status]"
                                       value="contracted"> Contracted
                            </label>
                        </li>
                    </ul>
                    <div class="wpacu-clearfix"></div>

                    <p>Some assets (CSS &amp; JavaScript) have inline code associate with them and often, they are quite large, making the asset row bigger and requiring you to scroll more until you reach a specific area. By setting it to "Contracted", it will hide all the inline code by default and you can view it by clicking on the toggle link inside the asset row.</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" class="setting_title">
                    <label>Input Fields Style:</label>
                    <p class="wpacu_subtitle"><small><em>How would you like to view the checkboxes / selectors?</em></small></p>
                    <p class="wpacu_read_more"><a href="https://assetcleanup.com/docs/?p=95" target="_blank">Read More</a></p>
                </th>
                <td>
                    <ul class="input_style_choices">
                        <li>
                            <label for="input_style_enhanced">
                                <input id="input_style_enhanced"
						               <?php if (! $data['input_style'] || $data['input_style'] === 'enhanced') { ?>checked="checked"<?php } ?>
                                       type="radio"
                                       name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[input_style]"
                                       value="enhanced"> Enhanced iPhone Style (Default)
                            </label>
                        </li>
                        <li>
                            <label for="input_style_standard">
                                <input id="input_style_standard"
						               <?php if ($data['input_style'] === 'standard') { ?>checked="checked"<?php } ?>
                                       type="radio"
                                       name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[input_style]"
                                       value="standard"> Standard
                            </label>
                        </li>
                    </ul>
                    <div class="wpacu-clearfix"></div>

                    <p>In case you prefer standard HTML checkboxes instead of the enhanced CSS3 iPhone style ones (on &amp; off) or you need a simple HTML layout in case you're using a screen reader software (e.g. for people with disabilities) which requires standard/clean HTML code, then you can choose "Standard" as an option.</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_hide_core_files">Hide WordPress Core Files From The Assets List?</label>
                </th>
                <td>
                    <label class="wpacu_switch">
                        <input id="wpacu_hide_core_files"
                               type="checkbox"
					        <?php echo (($data['hide_core_files'] == 1) ? 'checked="checked"' : ''); ?>
                               name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[hide_core_files]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    WordPress Core Files have handles such as 'jquery', 'wp-embed', 'comment-reply', 'dashicons' etc.
                    <p style="margin-top: 10px;">They should only be unloaded by experienced developers when they are convinced that are not needed in particular situations. It's better to leave them loaded if you have any doubts whether you need them or not. By hiding them in the assets management list, you will see a smaller assets list (easier to manage) and you will avoid updating by mistake any option (unload, async, defer) related to any core file.</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" class="setting_title">
                    <label for="wpacu_enable_test_mode">Enable Test Mode?</label>
                    <p class="wpacu_subtitle"><small><em>Apply plugin's changes for the admin only</em></small></p>
                    <p class="wpacu_read_more"><a target="_blank" href="https://assetcleanup.com/docs/?p=84">Read More</a></p>
                </th>
                <td>
                    <label class="wpacu_switch">
                        <input id="wpacu_enable_test_mode"
                               type="checkbox"
					        <?php echo (($data['test_mode'] == 1) ? 'checked="checked"' : ''); ?>
                               name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[test_mode]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    This is great for debugging when you're going through trial and error while removing unneeded CSS &amp; JavaScript on your website.
                    <p>Your visitors will load the website with all the settings &amp; assets loaded (just like it was before you activated the plugin).</p>
                    <p>For instance, you have an eCommerce website (e.g. WooCommerce, Easy Digital Downloads), and you're worried that unloading one wrong asset could break the "add to cart" functionality or the layout of the product page. You can enable this option, do the unloading for the CSS &amp; JavaScript files you believe are not needed on certain pages, test to check if everything is alright, and then disable test mode to enable the unloading for your visitors too (not only the admin).</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" class="setting_title">
                    <label for="wpacu_combine_loaded_css">Combine loaded CSS into one file?</label>
                    <p class="wpacu_subtitle"><small><em>Helps reducing the number of HTTP Requests even further</em></small></p>
                </th>
                <td>
                    <select id="wpacu_combine_loaded_css" name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[combine_loaded_css]">
                        <option <?php if ($data['combine_loaded_css'] === 'default') { ?>selected="selected"<?php } ?> value="">No (default)</option>
                        <option <?php if ($data['combine_loaded_css'] === 'for_admin') { ?>selected="selected"<?php } ?> value="for_admin">Yes, for logged-in administrators</option>
                        <option <?php if ($data['combine_loaded_css'] === 'for_all') { ?>selected="selected"<?php } ?> value="for_all">Yes, for everyone</option>
                    </select> <small>* if /wp-content/cache/ directory is not writable for some reason, this feature will not work; requires the DOMDocument XML DOM Parser to be enabled in PHP (which it is by default) for maximum performance</small>
                    &nbsp;
                    <p>This scans the remaining CSS files from the <code>&lt;head&gt;</code> and combines them into one file. To be 100% sure everything works fine after activation, consider using "Yes, for logged-in administrators" so only you can see the updated page. If all looks good, you can later switch it to "Yes (for everyone)".</p>
                    <p style="margin-bottom: 0;">The following stylesheets are not included in the combined CSS file for maximum performance:</p>
                    <ul style="list-style: disc; margin-left: 20px; margin-bottom: 0;">
                        <li>Have any <a target="_blank" href="https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content">preloading added to them</a> via <code>rel="preload"</code> will not be combined as they have priority in loading and shouldn't be mixed with the rest of the CSS.</li>
                        <li style="margin-bottom: 0;">Are loaded within the <code>&lt;body&gt;</code> part of the page as they were likely added there because they are needed after the page is rendered (e.g. for AJAX styling calls, popups or elements that are not visible in the top viewport etc.)</li>
                    </ul>
                    <p style="margin-top: 10px;"><strong>Note:</strong> If "Test Mode" is enabled, the CSS files this feature will not work for the guest users, even if "Yes, for everyone" is chosen as "Test Mode" purpose is to make the plugin as inactive for non logged-in administrators for ultimate debugging.</p>
                </td>
            </tr>
        </table>

        <hr />

        <h2><?php _e('Site-Wide Unload For Common WordPress Core CSS &amp; JS Files', WPACU_PLUGIN_TEXT_DOMAIN); ?></h2>

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
                    <label for="wpacu_disable_jquery_migrate">Disable jQuery Migrate Site-Wide?</label>
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
                    <label for="wpacu_disable_comment_reply">Disable Comment Reply Site-Wide?</label>
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

        <hr />
        <p><em><strong>Note:</strong> The options that have a lock are available to Pro users. <a href="<?php echo WPACU_PLUGIN_GO_PRO_URL; ?>?utm_source=plugin_settings">Click here to upgrade!</a></em></p>
        <hr />

        <h2><?php _e('Cleanup unused elements within HEAD section', WPACU_PLUGIN_TEXT_DOMAIN); ?></h2>
        <p>There are elements that are enabled by default in many WordPress environments, but not necessary to be enabled. Cleanup the unnecessary code between <code>&lt;head&gt;</code> and <code>&lt;/head&gt;</code>.</p>

        <table class="wpacu-form-table">
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
                               name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[remove_rsd_link]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <code>&lt;link rel=&quot;EditURI&quot; type=&quot;application/rsd xml&quot; title=&quot;RSD&quot; href=&quot;http://yourwebsite.com/xmlrpc.php?rsd&quot; /&gt;</code>
                    <p style="margin-top: 10px;">XML-RPC clients use this discover method. If you do not know what this is and don't use service integrations such as <a href="http://www.flickr.com/services/api/request.xmlrpc.html" target="_blank">Flickr</a> on your WordPress website, you can remove it.</p>
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
                               name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[remove_wlw_link]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <code>&lt;link rel=&quot;wlwmanifest&quot; type=&quot;application/wlwmanifest xml&quot; href=&quot;https://yourwebsite.com/asset-optimizer/wp-includes/wlwmanifest.xml&quot; /&gt;</code>
                    <p style="margin-top: 10px;">If you do not use Windows Live Writer to edit your blog contents, then it's safe to remove this.</p>
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
                               name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[remove_rest_api_link]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <code>&lt;link rel=&#39;https://api.w.org/&#39; href=&#39;https://yourwebsite.com/wp-json/&#39; /&gt;</code>
                    <p style="margin-top: 10px;">Are you accessing your content through endpoints (e.g. https://yourwebsite.com/wp-json/, https://yourwebsite.com/wp-json/wp/v2/posts/1 - <em>1</em> in this example is the POST ID)? If not, you can remove this.</p>
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
	                <?php echo $availableForPro; ?>
                    <label class="wpacu_switch wpacu_locked_for_pro">
                        <input id="wpacu_remove_posts_rel_links"
                               type="checkbox"
                               disabled="disabled"
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
	                <?php echo $availableForPro; ?>
                    <label class="wpacu_switch wpacu_locked_for_pro">
                        <input id="wpacu_remove_wp_version"
                               type="checkbox"
                               disabled="disabled"
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
	                <?php echo $availableForPro; ?>
                    <label class="wpacu_switch wpacu_locked_for_pro">
                        <input id="wpacu_remove_generator_tag"
                               type="checkbox"
                               disabled="disabled"
                               name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[remove_generator_tag]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <code>e.g. &lt;meta name=&quot;generator&quot; content=&quot;Easy Digital Downloads v2.9.8&quot; /&gt;</code>
                    <p style="margin-top: 10px;">This will remove all meta tags with the "generator" name, including the WordPress version. You could use a plugin or a theme that has added a generator notice, but you do not need to have it there. Moreover, it will hide the version of the plugins and theme you're using which is good for security reasons.</p>
                </td>
            </tr>

            <!-- Remove Main RSS Feed Link -->
            <tr valign="top">
                <th scope="row">
                    <label for="wpacu_remove_main_feed_link">Remove Main RSS Feed Link?</label>
                </th>
                <td>
	                <?php echo $availableForPro; ?>
                    <label class="wpacu_switch wpacu_locked_for_pro">
                        <input id="wpacu_remove_main_feed_link"
                               type="checkbox"
                               disabled="disabled"
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
	                <?php echo $availableForPro; ?>
                    <label class="wpacu_switch wpacu_locked_for_pro">
                        <input id="wpacu_remove_comment_feed_link"
                               type="checkbox"
                               disabled="disabled"
                               name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[remove_comment_feed_link]"
                               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
                    &nbsp;
                    <code>e.g. &lt;link rel=&quot;alternate&quot; type=&quot;application/rss xml&quot; title=&quot;Your Website Title &amp;raquo; Comments Feed&quot; href=&quot;https://www.yourdomain.com/comments/feed/&quot; /&gt;</code>
                    <p style="margin-top: 10px;">If you do not use the comments functionality on your posts or do not use WordPress for blogging purposes at all, then you can remove the comments feed link.</p>
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

                    <p style="margin-bottom: 0;"><strong>Disable XML-RPC Completely</strong>: If you do not use Jetpack plugin for off-site server communication or you only use the Dashboard to post content (without any remote software connection to the WordPress website such as Windows Live Writer or mobile apps), then you can disable the XML-RPC functionality. You can always re-enable it whenever you believe you'll need it.</p>
                </td>
            </tr>
        </table>

	    <?php
	    wp_nonce_field('wpacu_settings_update');
	    submit_button();
	    ?>
    </form>
</div>