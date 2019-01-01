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
    <nav class="wpacu-tab-nav-wrapper nav-tab-wrapper">
        <a href="<?php echo admin_url('admin.php?page=wpassetcleanup_tools&wpacu_for=reset'); ?>" class="nav-tab <?php if ($data['for'] === 'reset') { ?>nav-tab-active<?php } ?>">Reset</a>
        <a href="<?php echo admin_url('admin.php?page=wpassetcleanup_tools&wpacu_for=system_info'); ?>" class="nav-tab <?php if ($data['for'] === 'system_info') { ?>nav-tab-active<?php } ?>">System Info</a>
    </nav>

	<div class="wpacu-tools-container">
		<form id="wpacu-tools-form" action="<?php echo admin_url('admin.php?page='.WPACU_PLUGIN_ID.'_tools'); ?>" method="post">
            <?php if ($data['for'] === 'reset') { ?>
                <div><label for="wpacu-reset-drop-down">Do you need to reset the plugin to its initial settings or reset all changes?</label></div>

                <select name="wpacu-reset" id="wpacu-reset-drop-down">
                    <option value="">Select an option first...</option>
                    <option data-id="wpacu-warning-reset-settings" value="reset_settings">Reset settings</option>
                    <option data-id="wpacu-warning-reset-everything" value="reset_everything">Reset everything: settings, all unloads (bulk &amp; individual) &amp; load exceptions</option>
                </select>

                <div id="wpacu-license-data-remove-area">
                    <label for="wpacu-remove-license-data">
                       <input id="wpacu-remove-license-data" type="checkbox" name="wpacu-remove-license-data" value="1" /> Also remove license data in case the premium version was active at any point
                    </label>
                </div>

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
            <?php } elseif ($data['for'] === 'system_info') { ?>
	            <?php
	            wp_nonce_field('wpacu_get_system_info');
	            ?>
                <input type="hidden" name="wpacu-get-system-info" value="1" />

                <textarea disabled="disabled" style="color: rgba(51,51,51,1); background: #eee; white-space: pre; font-family: Menlo, Monaco, Consolas, 'Courier New', monospace; width: 80%; max-width: 100%;"
                          rows="20"><?php echo $data['system_info']; ?></textarea>

                <p><button name="submit"
                           id="wpacu-download-system-info-btn"
                           class="button button-primary"><?php esc_attr_e('Download System Info', WPACU_PLUGIN_TEXT_DOMAIN); ?></button></p>
            <?php } ?>
		</form>
	</div>
</div>
