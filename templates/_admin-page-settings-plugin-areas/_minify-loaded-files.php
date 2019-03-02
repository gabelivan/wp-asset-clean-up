<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}

$tabIdArea = 'wpacu-setting-minify-loaded-files';
$styleTabContent = ($selectedTabArea === $tabIdArea) ? 'style="display: table-cell;"' : '';
?>
<div id="<?php echo $tabIdArea; ?>" class="wpacu-settings-tab-content" <?php echo $styleTabContent; ?>>
    <h2><?php _e('Minify loaded CSS &amp; JavaScript files to reduce total page size', WPACU_PLUGIN_TEXT_DOMAIN); ?></h2>
    <table class="wpacu-form-table">
        <tr valign="top">
            <th scope="row" class="setting_title">
                <label for="wpacu_minify_css_enable">CSS Files Minification</label>
                <p class="wpacu_subtitle"><small><em>Helps decrease the total page size even further</em></small></p>
            </th>
            <td>
                <label class="wpacu_switch">
                    <input id="wpacu_minify_css_enable"
                           type="checkbox"
                        <?php echo (($data['minify_loaded_css'] == 1) ? 'checked="checked"' : ''); ?>
                           name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[minify_loaded_css]"
                           value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>

                &nbsp;This will take the remaining enqueued CSS files, minify them and load them from the cache.

				<?php
				$minifyCssExceptionsAreaStyle = ($data['minify_loaded_css'] == 1) ? 'opacity: 1;' : 'opacity: 0.4;';
				?>

				<div id="wpacu_minify_css_exceptions_area" style="<?php echo $minifyCssExceptionsAreaStyle; ?>">
					<div style="margin: 0 0 6px;">Do not minify the CSS files matching the patterns below (one per line):</div>
					<label for="wpacu_minify_css_exceptions">
                                    <textarea style="width: 100%;"
                                              rows="4"
                                              id="wpacu_minify_css_exceptions"
                                              name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[minify_loaded_css_exceptions]"><?php echo $data['minify_loaded_css_exceptions']; ?></textarea>
					</label>
					<div style="margin-top: 15px; margin-bottom: 0;"><hr /></div>
				</div>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" class="setting_title">
				<label for="wpacu_minify_js_enable">JavaScript Files Minification</label>
				<p class="wpacu_subtitle"><small><em>Helps decrease the total page size even further</em></small></p>
			</th>
			<td>
				<label class="wpacu_switch">
					<input id="wpacu_minify_js_enable"
					       type="checkbox"
						<?php echo (($data['minify_loaded_js'] == 1) ? 'checked="checked"' : ''); ?>
						   name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[minify_loaded_js]"
						   value="1" /> <span class="wpacu_slider wpacu_round"></span></label>
				&nbsp;&nbsp;This will take the remaining enqueued JavaScript files, minify them and load them from the cache.

				<?php
				$minifyJsExceptionsAreaStyle = ($data['minify_loaded_js'] == 1) ? 'opacity: 1;' : 'opacity: 0.4;';
				?>

				<div id="wpacu_minify_js_exceptions_area" style="<?php echo $minifyJsExceptionsAreaStyle; ?>">
					<div style="margin: 0 0 6px;">Do not minify the JavaScript files matching the patterns below (one per line):</div>
					<label for="wpacu_minify_js_exceptions">
                                    <textarea style="width: 100%;"
                                              rows="4"
                                              id="wpacu_minify_js_exceptions"
                                              name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[minify_loaded_js_exceptions]"><?php echo $data['minify_loaded_js_exceptions']; ?></textarea>
					</label>
					<div style="margin-top: 15px; margin-bottom: 0;"><hr /></div>
				</div>
			</td>
		</tr>
	</table>

    <hr />

    <ul style="list-style: none; margin-left: 18px;">
        <li style="margin-bottom: 18px;"><span style="color: #ffc107;" class="dashicons dashicons-lightbulb"></span> The CSS/JS cached files will be re-generated once the file version changes (the value from <code>?ver=</code>). In addition, the versioning value from the source will be appended to the new cached CSS/JS file name (e.g. new-file-name-here-ver-1.2).</li>
        <li><span style="color: #ffc107;" class="dashicons dashicons-lightbulb"></span> For maximum performance and to reduce server resources, the following CSS/JS files will not be minified, but kept as they are, since they are already optimised and minified by the WordPress core contributors &amp; developers:
            <div style="margin: 15px 0 0 28px;">
                <ul style="list-style: circle;">
                    <li>CSS/JS WordPress core files that end up in .min.css and .min.js (e.g. <code>/wp-includes/css/dashicons.min.css</code>, <code>/wp-includes/css/admin-bar.min.css</code>, <code>/wp-includes/js/jquery/jquery-migrate.min.js</code>, <code>/wp-includes/js/jquery/ui/datepicker.min.js</code> etc.)</li>
                    <li>jQuery library from <code>/wp-includes/js/jquery/jquery.js</code></li>
                </ul>
            </div>
        </li>
    </ul>
</div>
