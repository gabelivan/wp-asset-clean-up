<?php
/*
 * The file is included from _asset-style-rows.php
*/
if (! isset($data)) {
	exit; // no direct access
}
?>
<tr class="wpacu_asset_row <?php echo $data['row']['class']; ?>">
    <td valign="top">
        <p class="wpacu_handle">
            <label for="style_<?php echo $data['row']['obj']->handle; ?>"><?php _e('Handle:', WPACU_PLUGIN_NAME); ?> <strong><span style="color: green;"><?php echo $data['row']['obj']->handle; ?></span></strong></label>
			<?php
			if (isset($data['row']['obj']->wp) && $data['row']['obj']->wp) {
				?>
				<span class="dashicons dashicons-warning wordpress-core-file"><span class="tooltip">WordPress Core File<br />Not sure if needed or not? In this case, it's better to leave it loaded to avoid breaking the website.</span></span>
				<?php
			}
			?>
		</p>

		<div class="wpacu_asset_options_wrap">
			<ul class="wpacu_asset_options wpacu_exception_options_area" <?php /* [wpacu_lite] */ if ($data['row']['global_unloaded'] || $data['row']['is_post_type_unloaded']) { /* [/wpacu_lite] */ echo 'style="display: none;"'; } ?>>
				<li class="wpacu_unload_this_page">
					<label class="wpacu_switch"><input class="input-unload-on-this-page" id="style_<?php echo $data['row']['obj']->handle; ?>" <?php /* [wpacu_lite] */ if ($data['row']['global_unloaded'] || $data['row']['is_post_type_unloaded']) { /* [/wpacu_lite] */ echo 'disabled="disabled"'; } echo $data['row']['checked']; ?> name="<?php echo WPACU_PLUGIN_NAME; ?>[styles][]" type="checkbox" value="<?php echo $data['row']['obj']->handle; ?>" /><span class="wpacu_slider wpacu_round"></span></label> <label class="wpacu_slider_text" for="style_<?php echo $data['row']['obj']->handle; ?>">Unload on this page</label>
				</li>
			</ul>

			<?php
			/* [wpacu_lite] */ if ($data['row']['global_unloaded'] || $data['row']['is_post_type_unloaded']) { /* [/wpacu_lite] */
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
				<div class="clear"></div>
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
							Keep the unload global rule</label>
					</li>

					<li>
						<label><input data-handle="<?php echo $data['row']['obj']->handle; ?>"
						              class="wpacu_global_option wpacu_style"
						              type="radio"
						              name="wpacu_options_styles[<?php echo $data['row']['obj']->handle; ?>]"
						              value="remove" />
							Remove global unload rule</label>
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
				<div class="clear"></div>
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
								Keep rule</label>
						</li>

						<li>
							<label><input data-handle="<?php echo $data['row']['obj']->handle; ?>"
							              class="wpacu_bulk_option wpacu_style wpacu_remove_bulk_rule"
							              type="radio"
							              name="wpacu_options_post_type_styles[<?php echo $data['row']['obj']->handle; ?>]"
							              value="remove"/>
								Remove rule</label>
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
					Load it on this page (make exception<?php if (! $data['row']['is_global_rule']) { echo ' * works only IF any of bulk rule above is selected'; } ?>)</label>
			</li>
		</ul>
		<?php
		if (isset($data['row']['obj']->src) && $data['row']['obj']->src !== '') {
			?>
			<p><strong><?php _e('Source:', WPACU_PLUGIN_NAME); ?></strong> <a target="_blank" href="<?php echo $data['row']['obj']->srcHref; ?>"><?php echo $data['row']['obj']->src; ?></a></p>
		<?php }

		if (! empty($data['row']['extra_data_css_list'])) { ?>
			<p><strong><?php _e('Inline styling associated with the handle:', WPACU_PLUGIN_NAME); ?></strong></p>
			<p style="margin-bottom: 15px;"><?php foreach ($data['row']['extra_data_css_list'] as $extraDataCSS) {
					echo '<em>'.htmlspecialchars($extraDataCSS).'</em>'.'<br />';
				} ?></p>
			<?php
		}

		$extraInfo = array();

		if (! empty($data['row']['obj']->deps)) {
			$extraInfo[] = '<strong>'.__('Depends on:', WPACU_PLUGIN_NAME).'</strong> '.implode(', ', $data['row']['obj']->deps);
		}

		if ($data['row']['obj']->ver) {
			$extraInfo[] = '<strong>'.__('Version:', WPACU_PLUGIN_NAME).'</strong> '.$data['row']['obj']->ver;
		}

		// [wpacu_lite]
		$extraInfo[] = '<strong>'.__('File Size:', WPACU_PLUGIN_NAME).'</strong> <a href="'.WPACU_PLUGIN_GO_PRO_URL.'?utm_source=manage_asset&utm_medium=file_size" class="go-pro-link-no-style"><span class="tooltip">Upgrade to Pro to unlock all features</span><img width="20" height="20" src="'.WPACU_PLUGIN_URL.'/assets/icons/icon-lock.svg" valign="top" alt="" /> Pro Version</a>';
		// [/wpacu_lite]

		if (! empty($extraInfo)) {
			echo '<p>'.implode(' &nbsp;/&nbsp; ', $extraInfo).'</p>';
		}
		?>
	</td>
</tr>