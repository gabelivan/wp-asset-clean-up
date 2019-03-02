<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
	exit;
}

$tabIdArea = 'wpacu-setting-combine-loaded-files';
$styleTabContent = ($selectedTabArea === $tabIdArea) ? 'style="display: table-cell;"' : '';
?>
<div id="<?php echo $tabIdArea; ?>" class="wpacu-settings-tab-content" <?php echo $styleTabContent; ?>>
	<h2><?php _e('Combine loaded CSS &amp; JavaScript files into fewer files', WPACU_PLUGIN_TEXT_DOMAIN); ?></h2>

    <div style="line-height: 22px; background: #f8f8f8; border-left: 4px solid #008f9c; padding: 10px; margin: 0 0 15px;">
        <strong>NOTE:</strong> Concatenating assets is no longer a recommended practice in HTTP/2. &nbsp; <span style="color: #0073aa;" class="dashicons dashicons-info"></span> <a id="wpacu-http2-info-link" href="#wpacu-http2-info">Read more</a> &nbsp;|&nbsp; <a target="_blank" href="https://tools.keycdn.com/http2-test">Verify if your server has HTTP/2 support</a>
    </div>

	<table class="wpacu-form-table">
		<tr valign="top">
			<th scope="row" class="setting_title">
				<label for="wpacu_combine_loaded_css_enable">Combine loaded CSS (Stylesheets) into one file?</label>
				<p class="wpacu_subtitle"><small><em>Helps reducing the number of HTTP Requests even further</em></small></p>
			</th>
			<td>
				<label class="wpacu_switch">
					<input id="wpacu_combine_loaded_css_enable"
					       type="checkbox"
						<?php echo (in_array($data['combine_loaded_css'], array('for_admin', 'for_all', 1)) ? 'checked="checked"' : ''); ?>
						   name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[combine_loaded_css]"
						   value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>

				&nbsp;<small>* if /wp-content/cache/ directory is not writable for some reason, this feature will not work; requires the DOMDocument XML DOM Parser to be enabled in PHP (which it is by default) for maximum performance</small>
				&nbsp;
				<div id="combine_loaded_css_info_area" <?php if (in_array($data['combine_loaded_css'], array('for_admin', 'for_all', 1))) { ?> style="opacity: 1;" <?php } else { ?>style="opacity: 0.4;"<?php } ?>>
					<p style="margin-top: 8px; padding: 10px; background: #f2faf2;">
						<label for="combine_loaded_css_for_admin_only_checkbox">
							<input id="combine_loaded_css_for_admin_only_checkbox"
								<?php echo ((in_array($data['combine_loaded_css_for_admin_only'], array('for_admin', 1))
								             || $data['combine_loaded_css'] === 'for_admin')
									? 'checked="checked"' : ''); ?>
								   type="checkbox"
								   name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[combine_loaded_css_for_admin_only]"
								   value="1" />
							Apply combination only for logged-in administrator (for debugging purposes)
						</label>
					</p>

                    <div id="wpacu_combine_loaded_css_exceptions_area">
                        <div style="margin: 0 0 6px;">Do not combine the CSS files matching the patterns below (one per line, see pattern examples below):</div>
                        <label for="combine_loaded_css_exceptions">
                                    <textarea style="width: 100%;"
                                              rows="4"
                                              id="combine_loaded_css_exceptions"
                                              name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[combine_loaded_css_exceptions]"><?php echo $data['combine_loaded_css_exceptions']; ?></textarea>
                        </label>

                        <p>Pattern Examples (you don't have to add the full URL, as it's recommended to use relative paths):</p>
                        <code>/wp-includes/css/dashicons.min.css<br />/wp-includes/css/admin-bar.min.css<br />/wp-content/plugins/plugin-title/css/(.*?).css</code>

                        <div style="margin-top: 15px; margin-bottom: 0;"><hr /></div>
                    </div>

                    <p>This scans the remaining CSS files (left after cleaning up the unnecessary ones) from the <code>&lt;head&gt;</code> and <code>&lt;body&gt;</code> locations and combines them into ~2 files (one in each location). To be 100% sure everything works fine after activation, consider enabling this feature only for logged-in administrator, so only you can see the updated page. If all looks good, you can later uncheck the option to apply the feature to everyone else.</p>
                    <p style="margin-bottom: -7px;"><span style="color: #ffc107;" class="dashicons dashicons-lightbulb"></span> The following stylesheets are not included in the combined CSS file for maximum performance:</p>
                    <ul style="list-style: disc; margin-left: 35px; margin-bottom: 0;">
                        <li>Have any <a target="_blank" href="https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content">preloading added to them</a> via <code>rel="preload"</code> will not be combined as they have priority in loading and shouldn't be mixed with the rest of the CSS.</li>
                        <li style="margin-bottom: 0;">Have a different media attribute than "screen" and "all". If the "print" attribute is there, it is for a reason and it's not added together with "all".</li>
                    </ul>
                    <p style="margin-bottom: -7px; margin-top: 20px;"><span style="color: #ffc107;" class="dashicons dashicons-lightbulb"></span> This feature will not work <strong>IF</strong>:</p>
                    <ul style="margin-left: 35px; list-style: disc;">
                        <li>"Test Mode" is enabled, this feature will not work for the guest users, even if "Yes, for everyone" is chosen as "Test Mode" purpose is to make the plugin as inactive for non logged-in administrators for ultimate debugging.</li>
                        <li>The URL has query strings (e.g. an URL such as //www.yourdomain.com/product/title-here/?param=1&amp;param_two=value_here)</li>
                    </ul>
                </div>
            </td>
		</tr>

		<tr valign="top">
			<th scope="row" class="setting_title">
				<label for="wpacu_combine_loaded_js_enable">Combine loaded JS (JavaScript) into fewer files?</label>
				<p class="wpacu_subtitle"><small><em>Helps reducing the number of HTTP Requests even further</em></small></p>
			</th>
			<td>
				<label class="wpacu_switch">
					<input id="wpacu_combine_loaded_js_enable"
					       type="checkbox"
						<?php echo (in_array($data['combine_loaded_js'], array('for_admin', 'for_all', 1)) ? 'checked="checked"' : ''); ?>
						   name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[combine_loaded_js]"
						   value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>

				&nbsp;<small>* if /wp-content/cache/ directory is not writable for some reason, this feature will not work; requires the DOMDocument XML DOM Parser to be enabled in PHP (which it is by default) for maximum performance</small>

				<div id="combine_loaded_js_info_area" <?php if (in_array($data['combine_loaded_js'], array('for_admin', 'for_all', 1))) { ?> style="opacity: 1;" <?php } else { ?>style="opacity: 0.4;"<?php } ?>>
					<p style="margin-top: 8px; padding: 10px; background: #f2faf2;">
						<label for="combine_loaded_js_for_admin_only_checkbox">
							<input id="combine_loaded_js_for_admin_only_checkbox"
								<?php echo ((in_array($data['combine_loaded_js_for_admin_only'], array('for_admin', 1))
								             || $data['combine_loaded_js'] === 'for_admin')
									? 'checked="checked"' : ''); ?>
								   type="checkbox"
								   name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[combine_loaded_js_for_admin_only]"
								   value="1" />
							Apply combination only for logged-in administrator (for debugging purposes)
						</label>
					</p>

                    <p style="padding: 10px; background: #f2faf2;">
                        <label for="wpacu_combine_loaded_js_defer_body_checkbox">
                            <input id="wpacu_combine_loaded_js_defer_body_checkbox"
								<?php echo (($data['combine_loaded_js_defer_body'] == 1) ? 'checked="checked"' : ''); ?>
                                   type="checkbox"
                                   name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[combine_loaded_js_defer_body]"
                                   value="1" />
                            Defer loading JavaScript combined files from <code>&lt;body&gt;</code> (applies <code>defer="defer"</code> attribute to the combined script tags)
                        </label>
                    </p>

                    <hr />

                    <div id="wpacu_combine_loaded_js_exceptions_area">
                        <div style="margin: 0 0 6px;">Do not combine the JavaScript files matching the patterns below (one per line, see pattern examples below):</div>
                        <label for="combine_loaded_js_exceptions">
                                    <textarea style="width: 100%;"
                                              rows="4"
                                              id="combine_loaded_js_exceptions"
                                              name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[combine_loaded_js_exceptions]"><?php echo $data['combine_loaded_js_exceptions']; ?></textarea>
                        </label>

                        <p>Pattern Examples (you don't have to add the full URL, as it's recommended to use relative paths):</p>
                        <code>/wp-includes/js/admin-bar.min.js<br />/wp-includes/js/masonry.min.js<br />/wp-content/plugins/plugin-title/js/(.*?).js</code>

                        <div style="margin-top: 15px; margin-bottom: 0;"><hr /></div>
                    </div>

					<!--
                               //removeIf(development)
                               <p>
                                   <label for="wpacu_combine_loaded_js_move_to_body_checkbox">
                                       <input id="wpacu_combine_loaded_js_move_to_body_checkbox"
				                           <?php
					//echo (($data['combine_loaded_js_move_to_body'] == 1) ? 'checked="checked"' : '');
					?>
                                              type="checkbox"
                                              disabled="disabled"
                                              name="<?php //echo WPACU_PLUGIN_ID . '_settings'; ?>[combine_loaded_js_move_to_body]"
                                              value="1" />
                                       (in development) Move render-blocking JavaScript from <code>&lt;head&gt;</code> to <code>&lt;body&gt;</code> (except jQuery &amp; jQuery Migrate if they are loaded)
                                   </label>
                               </p>

                               <ul class="wpacu-radio-selections wpacu-vertical">
                                   <li>Select a combination level:</li>
                                   <li>
                                       <label>
                                           <input class="wpacu-radio-selection wpacu-combine-loaded-js-level"
                                                  data-target="wpacu_combine_loaded_js_level_one"
                                                  <?php //if (! $data['combine_loaded_js_level'] || $data['combine_loaded_js_level'] == 1) { ?>checked="checked"<?php //} ?>
                                                  type="radio" name="<?php //echo WPACU_PLUGIN_ID . '_settings'; ?>[combine_loaded_js_level]"
                                                  value="1" /> <span style="font-weight: 600;">Level 1</span>: Less JS combination groups (associated inline script tags are kept before or after the combined groups just as they were added in the first place for maximum compatibility) - <a id="wpacu-combine-js-level-one-info-link" href="#wpacu-combine-js-level-one-info">Read more</a>
                                       </label>
                                   </li>
                                   <li>
                                       <label>
                                           <input class="wpacu-radio-selection wpacu-combine-loaded-js-level"
                                                  data-target="wpacu_combine_loaded_js_level_two"
                                                  <?php //if ($data['combine_loaded_js_level'] == 2) { ?>checked="checked"<?php //} ?>
                                                  type="radio" name="<?php //echo WPACU_PLUGIN_ID . '_settings'; ?>[combine_loaded_js_level]"
                                                  value="2" /> <span style="font-weight: 600;">Level 2</span>: As less JS combination groups as possible (this combines all JS files into 2/3 files, keeping their HEAD and BODY locations and most of the inline script tags before them for maximum compatibility) - <a id="wpacu-combine-js-level-two-info-link" href="#wpacu-combine-js-level-two-info">Read more</a>
                                       </label>
                                   </li>
                               </ul>
                               //endRemoveIf(development)
                               -->
					<p>
						This results in as less JS combination groups as possible (this combines all JS files into 2/3 files, keeping their HEAD and BODY locations and most of the inline script tags before them for maximum compatibility) - <a id="wpacu-combine-js-method-info-link" href="#wpacu-combine-js-method-info">Read more</a>
					</p>

					<hr />

					<div class="clearfix"></div>

					<p><span style="color: #ffc107;" class="dashicons dashicons-lightbulb"></span> To be 100% sure everything works fine after activation, consider using the checkbox option above to apply the changes only for logged-in administrator (yourself). If all looks good, you can later uncheck so the changes will apply to everyone.</p>

					<hr />

					<p style="margin-bottom: -7px;"><span style="color: #ffc107;" class="dashicons dashicons-lightbulb"></span> Any scripts having "defer" or "async" attributes (which are there for a reason) will not be combined together with other render-blocking scripts.</p>

					<p style="margin-bottom: -7px; margin-top: 20px;"><span style="color: #ffc107;" class="dashicons dashicons-lightbulb"></span> This feature will not work <strong>IF</strong>:</p>
					<ul style="list-style: disc; margin-left: 35px; margin-bottom: 0;">
						<li>"Test Mode" is enabled and a guest (not logged-in) user visits the page, as the feature's ultimate purpose is to make the plugin inactive for non logged-in administrators for ultimate debugging.</li>
						<li>The URL has query strings (e.g. an URL such as //www.yourdomain.com/product/title-here/?param=1&amp;param_two=value_here)</li>
					</ul>
				</div>

				<!--
				//removeIf(development)
				<div id="wpacu-combine-js-level-one-info" class="wpacu-modal">
					<div class="wpacu-modal-content">
						<span class="wpacu-close">&times;</span>
						<h2>Combine JavaScript Files: Level 1</h2>
						<p style="margin-top: 0;">Scans the remaining JavaScript files (left after cleaning up the unnecessary ones) from the <code>&lt;head&gt;</code> and <code>&lt;body&gt;</code> and combines them into fewer files.</p>
						<p>If there is any inline JavaScript code associated with the script, then the script file will not be combined. This method will preserve the order of the <code>&lt;script&gt;</code> tags printed in the source code (inline JavaScript as well as external one) exactly as it is.</p>
						<p><strong>Example:</strong> If in the <code>&lt;body&gt;</code> area, you have 5 JS files one after another, 3 inline JavaScript script tags and then 4 JS files, the result will be: 1 combined JS file (from those 5), then 3 inline script tags and then 1 last combined JS (from the last 4).</p>
					</div>
				</div>
				//endRemoveIf(development)
				-->
				<div id="wpacu-combine-js-method-info" class="wpacu-modal">
					<div class="wpacu-modal-content">
						<span class="wpacu-close">&times;</span>
						<h2>How the JavaScript files are combined?</h2>
						<p style="margin-top: 0;">Scans the remaining JavaScript files (left after cleaning up the unnecessary ones) from the <code>&lt;head&gt;</code> and <code>&lt;body&gt;</code> locations and combines them into one file per each location.</p>
						<p>Any inline JavaScript code associated with the combined scripts, will not be altered or moved in any way.</p>
						<p><strong>Example:</strong> If you have 5 JS files (including jQuery library) loading in the <code>&lt;head&gt;</code> location and 7 JS files loading in <code>&lt;body&gt;</code> location, you will end up with a total of 3 JS files: jQuery library &amp; jQuery Migrate (they are not combined together with other JS files for maximum performance) in 1 file and the 2 JS files for HEAD and BODY, respectively.</p>
					</div>
				</div>
			</td>
		</tr>
	</table>
</div>

<div id="wpacu-http2-info" class="wpacu-modal" style="padding-top: 100px;">
    <div class="wpacu-modal-content" style="max-width: 800px;">
        <span class="wpacu-close">&times;</span>
        <h2 style="margin-top: 5px;">Combining CSS &amp; JavaScript files in HTTP/2 protocol</h2>
        <p>While it's still a good idea to combine assets into fewer (or only one) files in HTTP/1 (since you are restricted to the number of open connections), doing the same in HTTP/2 is no longer a performance optimization due to the ability to transfer multiple small files simultaneously without much overhead.</p>

        <hr />

        <p>In HTTP/2 some of the issues that were addressed are:</p>
        <ul>

            <li><strong>Multiplexing</strong>: allows concurrent requests across a single TCP connection</li>
            <li><strong>Server Push</strong>: whereby a server can push vital resources to the browser before being asked for them.</li>
        </ul>

        <hr />

        <p>Since HTTP requests are loaded concurrently in HTTP/2, it's better to only serve the files that your visitors need and don't worry much about concatenation.</p>
        <p>Note that page speed testing tools such as PageSpeed Insights, YSlow, Pingdom Tools or GTMetrix still recommend combining CSS/JS files because they haven't updated their recommendations based on HTTP/1 or HTTP/2 protocols so you should take into account the actual load time, not the performance grade.</p>

        <hr />

        <p style="margin-bottom: 12px;">If you do decide to move on with the concatenation (which at least would improve the GTMetrix performance grade from a cosmetic point of view), please remember to <strong>test thoroughly</strong> the pages that have the assets combined (pay attention to any JavaScript errors in the browser's console which is accessed via right click &amp; "Inspect") as, in rare cases, due to the order in which the scripts were loaded and the way their code was written, it could break some functionality.</p>
    </div>
</div>