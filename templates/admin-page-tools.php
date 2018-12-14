<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
	exit;
}

include_once '_top-area.php';

do_action('wpacu_admin_notices');
?>
<div class="wpacu-wrap wpacu-tools-area">
	<div class="wpacu-tools-container">
		<form id="wpacu-tools-form" action="<?php echo admin_url('admin.php?page='.WPACU_PLUGIN_ID.'_tools'); ?>" method="post">
			<div><label for="wpacu-reset-drop-down">Do you need to reset the plugin to its initial settings or reset all changes?</label></div>

            <select name="wpacu-reset" id="wpacu-reset-drop-down">
                <option value="">Select an option first...</option>
				<option data-id="wpacu-warning-reset-settings" value="reset_settings">Reset settings</option>
				<option data-id="wpacu-warning-reset-everything" value="reset_everything">Reset everything: settings, all unloads (bulk &amp; individual) &amp; load exceptions</option>
			</select>

			<div id="wpacu-warning-read"><span class="dashicons dashicons-warning"></span> <strong>Please read carefully below what the chosen action does as this process is NOT reversible.</strong></div>

			<div id="wpacu-warning-reset-settings" class="wpacu-warning">
				<p>This will reset every option from the "Settings" page/tab to the same state it was when you first activated the plugin.</p>
			</div>

			<div id="wpacu-warning-reset-everything" class="wpacu-warning">
				<p>This will reset everything (settings, page loads &amp; any load exceptions) to the same point it was when you first activated the plugin. All the plugin's database records will be removed. It will technically have the same effect for your website as if the plugin would be deactivated.</p>

				<p>This action is usually taken if:</p>
				<ul>
					<li>You believe you have applied some changes (such as unloading the wrong CSS / JavaScript file(s)) that broke the website and you need a quick fix to make it work the way it used to. Note that for this option, you can also enable "Test Mode" from the plugin's settings which will only apply the changes to you (logged-in administrator), while the regular visitors will view the website as if Asset CleanUp is deactivated.</li>
					<li>You want to uninstall Asset CleanUp and remove the traces left in the database (this is not the same thing as deactivating and activating the plugin again, as any changes applied would be preserved in this scenario)</li>
				</ul>
			</div>

            <?php
            wp_nonce_field('wpacu_tools_reset');
            ?>

            <input type="hidden" name="wpacu-tools-reset" value="1" />
            <input type="hidden" name="wpacu-action-confirmed" id="wpacu-action-confirmed" value="" />

            <div id="wpacu-reset-submit-area">
                <button name="submit"
                        disabled="disabled"
                        id="wpacu-reset-submit-btn"
                        class="button button-secondary"><?php esc_attr_e('Submit', WPACU_PLUGIN_TEXT_DOMAIN); ?></button>
            </div>
		</form>
	</div>
</div>

