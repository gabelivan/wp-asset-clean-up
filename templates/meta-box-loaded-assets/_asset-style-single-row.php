<?php
/*
 * The file is included from _asset-style-rows.php
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
            <label for="style_<?php echo $data['row']['obj']->handle; ?>"><?php _e('Handle:', WPACU_PLUGIN_TEXT_DOMAIN); ?> <strong><span style="color: green;"><?php echo $data['row']['obj']->handle; ?></span></strong></label>
	        <?php if (isset($data['view_by_location'])) { echo '&nbsp;<em>* Stylesheet (.css)</em>'; } ?>
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
			<ul class="wpacu_asset_options wpacu_exception_options_area" <?php if ($isBulkUnloaded) { echo 'style="display: none;"'; } ?>>
				<li class="wpacu_unload_this_page">
					<label class="wpacu_switch"><input class="input-unload-on-this-page" id="style_<?php echo $data['row']['obj']->handle; ?>" <?php /* [wpacu_lite] */ if ($isBulkUnloaded) { /* [/wpacu_lite] */ echo 'disabled="disabled"'; } echo $data['row']['checked']; ?>name="<?php echo WPACU_PLUGIN_ID; ?>[styles][]" type="checkbox" value="<?php echo $data['row']['obj']->handle; ?>" /><span class="wpacu_slider wpacu_round"></span></label> <label class="wpacu_slider_text" for="style_<?php echo $data['row']['obj']->handle; ?>">Unload on this page</label>
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
				<p><strong style="color: #d54e21;">This stylesheet file is unloaded everywhere</strong></p>
				<div class="wpacu-clearfix"></div>
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
						              class="wpacu_global_option wpacu_style"
						              type="radio"
						              name="wpacu_options_styles[<?php echo $data['row']['obj']->handle; ?>]"
						              checked="checked"
						              value="default" />
							Keep everywhere (site-wide) unload rule</label>
					</li>

					<li>
						<label><input data-handle="<?php echo $data['row']['obj']->handle; ?>"
						              class="wpacu_global_option wpacu_style"
						              type="radio"
						              name="wpacu_options_styles[<?php echo $data['row']['obj']->handle; ?>]"
						              value="remove" />
							Remove everywhere (site-wide) unload rule</label>
					</li>
					<?php
				} else {
					?>
					<li>
						<label><input data-handle="<?php echo $data['row']['obj']->handle; ?>"
						              class="wpacu_global_unload wpacu_global_style"
						              id="wpacu_global_unload_style_<?php echo $data['row']['obj']->handle; ?>" type="checkbox"
						              name="wpacu_global_unload_styles[]" value="<?php echo $data['row']['obj']->handle; ?>"/>
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
				<p><strong style="color: #d54e21;">This stylesheet file is unloaded on all <u><?php echo $data['post_type']; ?></u> post types.</strong></p>
				<div class="wpacu-clearfix"></div>
				<?php
			}
			?>

			<?php
			if ($data['bulk_unloaded_type'] === 'post_type') {
				?>
				<ul class="wpacu_asset_options">
					<?php
					// [START] ALL PAGES HAVING THE SAME POST TYPE
					if ($data['row']['is_post_type_unloaded']) {
						?>
						<li>
							<label><input data-handle="<?php echo $data['row']['obj']->handle; ?>"
							              class="wpacu_bulk_option wpacu_style wpacu_keep_bulk_rule"
							              type="radio"
							              name="wpacu_options_post_type_styles[<?php echo $data['row']['obj']->handle; ?>]"
							              checked="checked"
							              value="default"/>
								Keep bulk rule</label>
						</li>

						<li>
							<label><input data-handle="<?php echo $data['row']['obj']->handle; ?>"
							              class="wpacu_bulk_option wpacu_style wpacu_remove_bulk_rule"
							              type="radio"
							              name="wpacu_options_post_type_styles[<?php echo $data['row']['obj']->handle; ?>]"
							              value="remove"/>
								Remove bulk rule</label>
						</li>
						<?php
					} else {
						?>
						<li>
							<label><input data-handle="<?php echo $data['row']['obj']->handle; ?>"
							              class="wpacu_bulk_unload wpacu_post_type_unload wpacu_post_type_style"
							              id="wpacu_bulk_unload_post_type_style_<?php echo $data['row']['obj']->handle; ?>"
							              type="checkbox"
							              name="wpacu_bulk_unload_styles[post_type][<?php echo $data['post_type']; ?>][]"
							              value="<?php echo $data['row']['obj']->handle; ?>"/>
								Unload on All Pages of <strong><?php echo $data['post_type']; ?></strong> post type <small>* bulk unload</small></label>
						</li>
						<?php
					}
					?>
				</ul>
				<?php
			}
			// [END] ALL PAGES HAVING THE SAME POST TYPE
			?>

			<?php if ($data['bulk_unloaded_type'] === 'post_type') { ?>
		</div>
	<?php } ?>

            <div class="wpacu-clearfix"></div>
        </div>

		<?php
		// [wpacu_pro]
		do_action('wpacu_pro_bulk_unload_output', $data, $data['row']['obj'], 'css');
		// [/wpacu_pro]
		?>

		<ul class="wpacu_asset_options wpacu_exception_options_area">
			<li id="wpacu_load_it_option_style_<?php echo $data['row']['obj']->handle; ?>">
				<label><input data-handle="<?php echo $data['row']['obj']->handle; ?>"
				              id="wpacu_style_load_it_<?php echo $data['row']['obj']->handle; ?>"
				              class="wpacu_load_it_option wpacu_style wpacu_load_exception"
				              type="checkbox"
						<?php if ($data['row']['is_load_exception']) { ?> checked="checked" <?php } ?>
						      name="wpacu_styles_load_it[]"
						      value="<?php echo $data['row']['obj']->handle; ?>"/>
					Load it on this page (make exception<?php if (! $isBulkUnloaded) { echo ' * works only IF any of bulk rule above is selected'; } ?>)</label>
			</li>
		</ul>

		<?php if (isset($data['row']['obj']->src, $data['row']['obj']->srcHref) && $data['row']['obj']->src && $data['row']['obj']->srcHref) { ?>
			<p><strong><?php _e('Source:', WPACU_PLUGIN_TEXT_DOMAIN); ?></strong> <a target="_blank" href="<?php echo $data['row']['obj']->srcHref; ?>"><?php echo $data['row']['obj']->src; ?></a></p>
		<?php }

		if (! empty($data['row']['extra_data_css_list'])) { ?>
			<div>
                <?php _e('Inline styling associated with the handle:', WPACU_PLUGIN_TEXT_DOMAIN); ?>
                <a class="wpacu-assets-inline-code-collapsible"
                   <?php if ($inlineCodeStatus !== 'contracted') { echo 'wpacu-assets-inline-code-collapsible-active'; } ?>
                   href="#">Show / Hide</a>
                <div class="wpacu-assets-inline-code-collapsible-content <?php if ($inlineCodeStatus !== 'contracted') { echo 'wpacu-open'; } ?>">
                    <div>
                        <p style="margin-bottom: 15px; line-height: normal !important;">
                            <?php foreach ($data['row']['extra_data_css_list'] as $extraDataCSS) {
                                echo '<em>'.htmlspecialchars($extraDataCSS).'</em>'.'<br />';
                            }
                            ?>
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

		if ($data['row']['obj']->ver) {
			$extraInfo[] = '<strong>'.__('Version:', WPACU_PLUGIN_TEXT_DOMAIN) . '</strong> ' . $data['row']['obj']->ver;
		}

		// [wpacu_lite]
		if (isset($data['row']['obj']->src) && $data['row']['obj']->src) {
		    $extraInfo[] = '<strong>'.__('File Size:', WPACU_PLUGIN_TEXT_DOMAIN) . '</strong> <a href="' . WPACU_PLUGIN_GO_PRO_URL . '?utm_source=manage_asset&utm_medium=file_size" class="go-pro-link-no-style"><span class="wpacu-tooltip">Upgrade to Pro and unlock all features</span><img width="20" height="20" src="' . WPACU_PLUGIN_URL . '/assets/icons/icon-lock.svg" valign="top" alt="" /> Pro Version</a>';
		}
		// [/wpacu_lite]

		if (! empty($extraInfo)) {
			echo '<p>'.implode(' &nbsp;/&nbsp; ', $extraInfo).'</p>';
		}
		?>
	</td>
</tr>