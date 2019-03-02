<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
	exit;
}

$tabIdArea = 'wpacu-setting-plugin-usage-settings';
$styleTabContent = ($selectedTabArea === $tabIdArea) ? 'style="display: table-cell;"' : '';
?>
<div id="<?php echo $tabIdArea; ?>" class="wpacu-settings-tab-content" <?php echo $styleTabContent; ?>>
    <h2><?php _e('Plugin Usage Preferences', WPACU_PLUGIN_TEXT_DOMAIN); ?></h2>
    <p>Choose how the assets are retrieved and whether you would like to see them within the Dashboard / Front-end view; Decide how the management list of CSS &amp; JavaScript files will show up and get sorted, depending on your preferences.</p>
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
                <label>
                    <select id="wpacu_assets_list_layout"
                            name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[assets_list_layout]">
                        <option <?php if ($data['assets_list_layout'] === 'by-location') { echo 'selected="selected"'; } ?> value="by-location">All Styles &amp; Scripts &#10230; One list grouped by location (themes, plugins, core &amp; external)</option>
                        <option <?php if (in_array($data['assets_list_layout'], array('two-lists', 'default'))) { echo 'selected="selected"'; } ?> value="two-lists">All Styles + All Scripts &#10230; Two lists</option>
                        <option disabled="disabled" value="all">All Styles &amp; Scripts &#10230; One list (Pro Version)</option>
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
    </table>
</div>