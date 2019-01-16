<?php
/*
 * The file is included from _asset-script-rows.php
*/
if (! isset($data)) {
	exit; // no direct access
}

$inlineCodeStatus = $data['plugin_settings']['assets_list_inline_code_status'];
$isCoreFile       = (isset($data['row']['obj']->wp) && $data['row']['obj']->wp);
$hideCoreFiles    = $data['plugin_settings']['hide_core_files'];
$isBulkUnloaded   = ($data['row']['global_unloaded'] || $data['row']['is_post_type_unloaded']);
?>
<tr class="wpacu_asset_row <?php echo $data['row']['class']; ?>" style="<?php if ($isCoreFile && $hideCoreFiles) { echo 'display: none;'; } ?>">
	<td valign="top">
		<p class="wpacu_handle">
			<label for="script_<?php echo $data['row']['obj']->handle; ?>"> <?php _e('Handle:', WPACU_PLUGIN_TEXT_DOMAIN); ?> <strong><span style="color: green;"><?php echo $data['row']['obj']->handle; ?></span></strong></label>
			<?php
			if ($isCoreFile && ! $hideCoreFiles) {
            ?>
				<span class="dashicons dashicons-warning wordpress-core-file"><span class="wpacu-tooltip">WordPress Core File<br />Not sure if needed or not? In this case, it's better to leave it loaded to avoid breaking the website.</span></span>
            <?php
			}
			?>
		</p>

        <div <?php if (! $isBulkUnloaded) { ?>class="wrap_bulk_unload_options"<?php } ?>>
            <div class="wpacu_asset_options_wrap">
                <ul class="wpacu_asset_options wpacu_exception_options_area" <?php /* [wpacu_lite] */ if ($data['row']['global_unloaded'] || $data['row']['is_post_type_unloaded']) { /* [/wpacu_lite] */ echo 'style="display: none;"'; } ?>>
                    <li class="wpacu_unload_this_page">
                        <label class="wpacu_switch">
                            <input class="input-unload-on-this-page" id="script_<?php echo $data['row']['obj']->handle; ?>" <?php /* [wpacu_lite] */ if ($data['row']['global_unloaded'] || $data['row']['is_post_type_unloaded']) { /* [/wpacu_lite] */ echo 'disabled="disabled"'; } echo $data['row']['checked']; ?>name="<?php echo WPACU_PLUGIN_ID; ?>[scripts][]" type="checkbox" value="<?php echo $data['row']['obj']->handle; ?>" /><span class="wpacu_slider wpacu_round"></span>
                        </label>
                        <label class="wpacu_slider_text" for="script_<?php echo $data['row']['obj']->handle; ?>">
                            Unload on this page
                        </label>
                    </li>
                </ul>

                <?php
                if ($isBulkUnloaded) {
                    ?>
                    <em>"Unload on this page" rule is locked and irrelevant as there are global rules set below that overwrite it. Once all the rules below are removed, this option will become available again.</em>
                    <?php
                }
                ?>
            </div>

            <div class="wpacu_asset_options_wrap">
                <?php
                // Unloaded Everywhere
                if ($data['row']['global_unloaded']) {
                    ?>
                    <p><strong style="color: #d54e21;">This JavaScript file is unloaded everywhere</strong></p>
                    <?php
                }
                ?>

                <ul class="wpacu_asset_options">
                    <?php
                    // [START] UNLOAD EVERYWHERE
                    if ($data['row']['global_unloaded']) {
                        ?>
                        <li>
                            <label><input data-handle="<?php echo $data['row']['obj']->handle; ?>"
                                          class="wpacu_bulk_option wpacu_script"
                                          type="radio"
                                          name="wpacu_options_scripts[<?php echo $data['row']['obj']->handle; ?>]"
                                          checked="checked"
                                          value="default" />
                                Keep unload everywhere (site-wide) rule</label>
                        </li>

                        <li>
                            <label><input data-handle="<?php echo $data['row']['obj']->handle; ?>"
                                          class="wpacu_bulk_option wpacu_script"
                                          type="radio"
                                          name="wpacu_options_scripts[<?php echo $data['row']['obj']->handle; ?>]"
                                          value="remove" />
                                Remove unload everywhere (site-wide) rule</label>
                        </li>
                        <?php
                    } else {
                        ?>
                        <li>
                            <label><input data-handle="<?php echo $data['row']['obj']->handle; ?>"
                                          class="wpacu_global_unload wpacu_global_script"
                                          id="wpacu_global_unload_script_<?php echo $data['row']['obj']->handle; ?>"
                                          type="checkbox"
                                          name="wpacu_global_unload_scripts[]"
                                          value="<?php echo $data['row']['obj']->handle; ?>"/>
                                Unload Everywhere <small>* bulk unload</small></label>
                        </li>
                        <?php
                    }
                    // [END] UNLOAD EVERYWHERE
                    ?>

                </ul>
            </div>

            <?php if ($data['bulk_unloaded_type'] === 'post_type') { ?>
            <div class="wpacu_asset_options_wrap">
            <?php } ?>

                <?php
                // Unloaded On All Pages Belonging to the page's Post Type
                if ($data['row']['is_post_type_unloaded']) {
                    ?>
                    <p><strong style="color: #d54e21;">This JavaScript file is unloaded on all <u><?php echo $data['post_type']; ?></u> post types.</strong></p>
                    <div class="wpacu-clearfix"></div>
                    <?php
                }
                ?>

                <ul class="wpacu_asset_options">
                    <?php
                    if ($data['bulk_unloaded_type'] === 'post_type') {
                        // [START] ALL PAGES HAVING THE SAME POST TYPE
                        if ($data['row']['is_post_type_unloaded']) {
                            ?>
                            <li>
                                <label><input data-handle="<?php echo $data['row']['obj']->handle; ?>"
                                              class="wpacu_post_type_option wpacu_post_type_script wpacu_keep_bulk_rule"
                                              type="radio"
                                              name="wpacu_options_post_type_scripts[<?php echo $data['row']['obj']->handle; ?>]"
                                              checked="checked"
                                              value="default"/>
                                    Keep rule</label>
                            </li>

                            <li>
                                <label><input data-handle="<?php echo $data['row']['obj']->handle; ?>"
                                              class="wpacu_post_type_option wpacu_remove_bulk_rule wpacu_post_type_script"
                                              type="radio"
                                              name="wpacu_options_post_type_scripts[<?php echo $data['row']['obj']->handle; ?>]"
                                              value="remove"/>
                                    Remove rule</label>
                            </li>
                            <?php
                        } else {
                            ?>
                            <li>
                                <label><input data-handle="<?php echo $data['row']['obj']->handle; ?>"
                                              class="wpacu_bulk_unload wpacu_post_type_unload wpacu_post_type_script"
                                              id="wpacu_global_unload_post_type_script_<?php echo $data['row']['obj']->handle; ?>"
                                              type="checkbox"
                                              name="wpacu_bulk_unload_scripts[post_type][<?php echo $data['post_type']; ?>][]"
                                              value="<?php echo $data['row']['obj']->handle; ?>"/>
                                    Unload on All Pages of <strong><?php echo $data['post_type']; ?></strong> post type <small>* bulk unload</small></label>
                            </li>
                            <?php
                        }
                    }
                    // [END] ALL PAGES HAVING THE SAME POST TYPE
                    ?>
                </ul>
                <?php if ($data['bulk_unloaded_type'] === 'post_type') { ?>
            </div>
            <?php } ?>
            <div class="wpacu-clearfix"></div>
        </div>
		<?php
		do_action('wpacu_pro_bulk_unload_output', $data, $data['row']['obj'], 'js');
		?>

		<ul class="wpacu_asset_options wpacu_exception_options_area">
			<li id="wpacu_load_it_option_script_<?php echo $data['row']['obj']->handle; ?>">
				<label><input data-handle="<?php echo $data['row']['obj']->handle; ?>"
				              id="wpacu_script_load_it_<?php echo $data['row']['obj']->handle; ?>"
				              class="wpacu_load_it_option wpacu_script wpacu_load_exception"
				              type="checkbox"
				              name="wpacu_scripts_load_it[]"
						<?php if ($data['row']['is_load_exception']) { ?> checked="checked" <?php } ?>
						      value="<?php echo $data['row']['obj']->handle; ?>" />
					Load it on this page (make exception<?php if (! $isBulkUnloaded) { echo ' * works only IF any of bulk rule above is selected'; } ?>)</label>
			</li>
		</ul>
		<?php
		if (isset($data['row']['obj']->src, $data['row']['obj']->srcHref) && $data['row']['obj']->src !== '' && $data['row']['obj']->srcHref) {
			?>
			<p><strong><?php _e('Source:', WPACU_PLUGIN_TEXT_DOMAIN); ?></strong> <a target="_blank" href="<?php echo $data['row']['obj']->srcHref; ?>"><?php echo $data['row']['obj']->src; ?></a></p>
			<?php
		}

		if ($data['row']['extra_data_js']) { ?>
            <div><strong><?php _e('Inline JavaScript code associated with the handle:', WPACU_PLUGIN_TEXT_DOMAIN); ?></strong>
                <a class="wpacu-assets-inline-code-collapsible"
	               <?php if ($inlineCodeStatus !== 'contracted') { echo 'wpacu-assets-inline-code-collapsible-active'; } ?>
                   href="#">Show / Hide</a>
                <div class="wpacu-assets-inline-code-collapsible-content <?php if ($inlineCodeStatus !== 'contracted') { echo 'wpacu-open'; } ?>">
                    <div>
                        <p style="margin-top: -7px !important; line-height: normal !important;">
                            <em><?php echo strip_tags($data['row']['extra_data_js']); ?></em>
                        </p>
                    </div>
                </div>
            </div>
            <?php
		}

		$extraInfo = array();

		if (! empty($data['row']['obj']->deps)) {
			$extraInfo[] = '<strong>'.__('Depends on:', WPACU_PLUGIN_TEXT_DOMAIN) . '</strong> ' . implode(', ', $data['row']['obj']->deps);
		}

		if (isset($data['row']['obj']->ver) && $data['row']['obj']->ver !== '') {
			$extraInfo[] = '<strong>'.__('Version:', WPACU_PLUGIN_TEXT_DOMAIN) . '</strong> ' . $data['row']['obj']->ver;
		}

		if (isset($data['row']['obj']->position) && $data['row']['obj']->position !== '') {
			$extraInfo[] = '<strong>'.__('Position:', WPACU_PLUGIN_TEXT_DOMAIN) . '</strong> ' . (( $data['row']['obj']->position === 'head') ? 'HEAD' : 'BODY');
		}

		// [wpacu_lite]
		$extraInfo[] = '<strong>'.__('File Size:', WPACU_PLUGIN_TEXT_DOMAIN) . '</strong> <a class="go-pro-link-no-style" href="' . WPACU_PLUGIN_GO_PRO_URL . '?utm_source=manage_asset&utm_medium=file_size"><span class="wpacu-tooltip">Upgrade to Pro and unlock all features</span><img width="20" height="20" src="' . WPACU_PLUGIN_URL . '/assets/icons/icon-lock.svg" valign="top" alt="" /> Pro Version</a>';
		// [/wpacu_lite]

		if (! empty($extraInfo)) {
			echo '<p>'.implode(' &nbsp;/&nbsp; ', $extraInfo).'</p>';
		}
		?>

		<!-- [wpacu_lite] -->
		<?php if (isset($data['row']['obj']->src) && $data['row']['obj']->src !== '') { ?>
			<div class="wpacu-script-attributes-area wpacu-lite">
				<p>If loaded (not unloaded by any of the rules above), apply the following attributes: <em><a class="go-pro-link-no-style" href="<?php echo WPACU_PLUGIN_GO_PRO_URL; ?>">* this option is available in Pro version</a></em></p>

				<ul class="wpacu-script-attributes-settings wpacu-first">
					<li><a class="go-pro-link-no-style" href="<?php echo WPACU_PLUGIN_GO_PRO_URL; ?>"><span class="wpacu-tooltip wpacu-larger">This feature is available in the premium version of the plugin.<br /> Click here to upgrade to Pro!</span><img width="20" height="20" src="<?php echo WPACU_PLUGIN_URL; ?>/assets/icons/icon-lock.svg" valign="top" alt="" /></a>&nbsp; <strong>async</strong> &#10230;</li>
					<li><label for="async_none_<?php echo $data['row']['obj']->handle; ?>"><input disabled="disabled" id="async_none_<?php echo $data['row']['obj']->handle; ?>" type="radio" name="wpacu_async[<?php echo $data['row']['obj']->handle; ?>]" value="none" />none (default)</label></li>
					<li><label for="async_on_this_page_<?php echo $data['row']['obj']->handle; ?>"><input disabled="disabled" id="async_on_this_page_<?php echo $data['row']['obj']->handle; ?>" type="radio" name="wpacu_async[<?php echo $data['row']['obj']->handle; ?>]" value="on_this_page" />on this page</label></li>
					<li><label for="async_everywhere_<?php echo $data['row']['obj']->handle; ?>"><input disabled="disabled" id="async_everywhere_<?php echo $data['row']['obj']->handle; ?>" type="radio" name="wpacu_async[<?php echo $data['row']['obj']->handle; ?>]" value="everywhere" />everywhere</label></li>
				</ul>


				<ul class="wpacu-script-attributes-settings">
					<li><a class="go-pro-link-no-style" href="<?php echo WPACU_PLUGIN_GO_PRO_URL; ?>"><span class="wpacu-tooltip wpacu-larger">This feature is available in the premium version of the plugin.<br /> Click here to upgrade to Pro!</span><img width="20" height="20" src="<?php echo WPACU_PLUGIN_URL; ?>/assets/icons/icon-lock.svg" valign="top" alt="" /></a>&nbsp; <strong>defer</strong> &#10230;</li>
					<li><label for="defer_none_<?php echo $data['row']['obj']->handle; ?>"><input disabled="disabled" id="defer_none_<?php echo $data['row']['obj']->handle; ?>" type="radio" name="wpacu_defer[<?php echo $data['row']['obj']->handle; ?>]" value="none" />none (default)</label></li>
					<li><label for="defer_on_this_page_<?php echo $data['row']['obj']->handle; ?>"><input disabled="disabled" id="defer_on_this_page_<?php echo $data['row']['obj']->handle; ?>" type="radio" name="wpacu_defer[<?php echo $data['row']['obj']->handle; ?>]" value="on_this_page" />on this page</label></li>
					<li><label for="defer_everywhere_<?php echo $data['row']['obj']->handle; ?>"><input disabled="disabled" id="defer_everywhere_<?php echo $data['row']['obj']->handle; ?>" type="radio" name="wpacu_defer[<?php echo $data['row']['obj']->handle; ?>]" value="everywhere" />everywhere</label></li>
				</ul>
			</div>
		<?php } ?>
		<!-- [/wpacu_lite] -->
	</td>
</tr>