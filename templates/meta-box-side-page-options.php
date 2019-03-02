<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
	exit;
}
?>
<div id="wpacu_meta_box_page_options_content">
    <ul>
        <li>
            <label for="wpacu_page_options_no_css_minify">
                <input type="checkbox"
					<?php if (isset($data['page_options']['no_css_minify']) && $data['page_options']['no_css_minify']) { echo 'checked="checked"'; } ?>
                       id="wpacu_page_options_no_css_minify"
                       name="<?php echo WPACU_PLUGIN_ID; ?>_page_options[no_css_minify]"
                       value="1" />Do not minify CSS on this page
            </label>
        </li>
        <li>
            <label for="wpacu_page_options_no_css_optimize">
                <input type="checkbox"
					<?php if (isset($data['page_options']['no_css_optimize']) && $data['page_options']['no_css_optimize']) { echo 'checked="checked"'; } ?>
                       id="wpacu_page_options_no_css_optimize"
                       name="<?php echo WPACU_PLUGIN_ID; ?>_page_options[no_css_optimize]"
                       value="1" />Do not combine CSS on this page
            </label>
        </li>

        <li>
            <label for="wpacu_page_options_no_js_minify">
                <input type="checkbox"
					<?php if (isset($data['page_options']['no_js_minify']) && $data['page_options']['no_js_minify']) { echo 'checked="checked"'; } ?>
                       id="wpacu_page_options_no_js_minify"
                       name="<?php echo WPACU_PLUGIN_ID; ?>_page_options[no_js_minify]"
                       value="1" />Do not minify JS files on this page
            </label>
        </li>
        <li>
            <label for="wpacu_page_options_no_js_optimize">
                <input type="checkbox"
					<?php if (isset($data['page_options']['no_js_optimize']) && $data['page_options']['no_js_optimize']) { echo 'checked="checked"'; } ?>
                       id="wpacu_page_options_no_js_optimize"
                       name="<?php echo WPACU_PLUGIN_ID; ?>_page_options[no_js_optimize]"
                       value="1" />Do not combine JavaScript on this page
            </label>
        </li>

        <li>
            <label for="wpacu_page_options_no_assets_settings">
                <input type="checkbox"
					<?php if (isset($data['page_options']['no_assets_settings']) && $data['page_options']['no_assets_settings']) { echo 'checked="checked"'; } ?>
                       id="wpacu_page_options_no_assets_settings"
                       name="<?php echo WPACU_PLUGIN_ID; ?>_page_options[no_assets_settings]"
                       value="1" />Do not apply any CSS &amp; JavaScript settings (including async, defer, load &amp; unload rules) on this page
            </label>
        </li>
    </ul>
    <hr/>
    <p style="margin-top: 10px;"><strong><span style="color: #82878c;" class="dashicons dashicons-lightbulb"></span></strong> Use the "Preview" button if you wish to see how the options above, as well as the load/unload rules will apply before updating anything. It works like the "Test Mode" feature for this page only.</p>
</div>