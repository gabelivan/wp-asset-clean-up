<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
	exit;
}

$tabIdArea = 'wpacu-setting-test-mode';
$styleTabContent = ($selectedTabArea === $tabIdArea) ? 'style="display: table-cell;"' : '';
?>
<div id="<?php echo $tabIdArea; ?>" class="wpacu-settings-tab-content" <?php echo $styleTabContent; ?>>
	<h2><?php _e('Test Mode', WPACU_PLUGIN_TEXT_DOMAIN); ?></h2>
	<p>Have your visitors load the website without any Asset CleanUp settings while you're going through the plugin setup and unloading the useless CSS &amp; JavaScript!</p>
	<table class="wpacu-form-table">
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
	</table>
</div>