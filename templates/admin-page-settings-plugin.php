<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}

include_once '_top-area.php';

// [wpacu_lite]
$availableForPro  = '<a href="'.WPACU_PLUGIN_GO_PRO_URL.'?utm_source=plugin_settings" class="go-pro-link-no-style"><span class="wpacu-tooltip">Available for Pro users<br />Buy now to unlock all features!</span> <img width="20" height="20" src="'.WPACU_PLUGIN_URL.'/assets/icons/icon-lock.svg" valign="top" alt="" /></a> &nbsp; ';
$settingsWithLock = '<em><strong>Note:</strong> The settings that have a lock are available to Pro users. <a href="' . WPACU_PLUGIN_GO_PRO_URL . '?utm_source=plugin_settings">Click here to upgrade!</a></em>';
// [/wpacu_lite]

do_action('wpacu_admin_notices');

$wikiStatus = ($data['wiki_read'] == 1) ? '<small style="font-weight: 200; color: green;">* '.__('read', WPACU_PLUGIN_TEXT_DOMAIN).'</small>'
                                        : '<small style="font-weight: 200; color: #cc0000;">* '.__('unread', WPACU_PLUGIN_TEXT_DOMAIN).'</small>';

$settingsTabs = array(
	'wpacu-setting-strip-the-fat'         => __('Stripping the "fat"', WPACU_PLUGIN_TEXT_DOMAIN) . ' '.$wikiStatus,
    'wpacu-setting-plugin-usage-settings' => __('General &amp; Files Management', WPACU_PLUGIN_TEXT_DOMAIN),
    'wpacu-setting-test-mode'             => __('Test Mode', WPACU_PLUGIN_TEXT_DOMAIN),
    'wpacu-setting-minify-loaded-files'   => __('Minify CSS &amp; JS Files', WPACU_PLUGIN_TEXT_DOMAIN),
    'wpacu-setting-combine-loaded-files'  => __('Combine CSS &amp; JS Files', WPACU_PLUGIN_TEXT_DOMAIN),
    'wpacu-setting-common-files-unload'   => __('Site-Wide Common Unloads', WPACU_PLUGIN_TEXT_DOMAIN),
    'wpacu-setting-head-cleanup'          => __('&lthead&gt; CleanUp', WPACU_PLUGIN_TEXT_DOMAIN),
    'wpacu-setting-disable-xml-rpc'       => __('Disable XML-RPC', WPACU_PLUGIN_TEXT_DOMAIN),
);

$settingsTabActive = 'wpacu-setting-plugin-usage-settings';

// Is 'Stripping the "fat"' marked as read? Mark the "General & Files Management" as the default tab
$defaultTabArea = ($data['wiki_read'] == 1) ? 'wpacu-setting-plugin-usage-settings' : 'wpacu-setting-strip-the-fat';

$selectedTabArea = isset($_POST['wpacu_selected_tab_area']) && array_key_exists($_POST['wpacu_selected_tab_area'], $settingsTabs) // the tab id area has to be one within the list above
	? $_POST['wpacu_selected_tab_area'] // after update
	: $defaultTabArea; // default

if ($selectedTabArea && array_key_exists($selectedTabArea, $settingsTabs)) {
	$settingsTabActive = $selectedTabArea;
}
?>
<div class="wpacu-wrap wpacu-settings-area <?php if ($data['input_style'] !== 'standard') { ?>wpacu-switch-enhanced<?php } else { ?>wpacu-switch-standard<?php } ?>">
    <form method="post" action="" id="wpacu-settings-form">
        <input type="hidden" name="wpacu_settings_page" value="1" />

        <div id="wpacu-settings-vertical-tab-wrap">
            <div class="wpacu-settings-tab">
            <?php
            foreach ($settingsTabs as $navId => $navText) {
                $active = ($settingsTabActive === $navId) ? 'active' : '';
            ?>
                <a href="#<?php echo $navId; ?>" class="wpacu-settings-tab-link <?php echo $active; ?>" onclick="wpacuTabOpenSettingsArea(event, '<?php echo $navId; ?>');"><?php echo $navText; ?></a>
            <?php
            }
            ?>
            </div>

            <?php
            include_once '_admin-page-settings-plugin-areas/_strip-the-fat.php';
            include_once '_admin-page-settings-plugin-areas/_plugin-usage-settings.php';
            include_once '_admin-page-settings-plugin-areas/_test-mode.php';
            include_once '_admin-page-settings-plugin-areas/_minify-loaded-files.php';
            include_once '_admin-page-settings-plugin-areas/_combine-loaded-files.php';
            include_once '_admin-page-settings-plugin-areas/_common-files-unload.php';
            include_once '_admin-page-settings-plugin-areas/_head-cleanup.php';
            include_once '_admin-page-settings-plugin-areas/_disable-xml-rpc-protocol.php';
            ?>

            <div class="clearfix"></div>
        </div>

        <div id="wpacu-update-button-area">
            <?php
            wp_nonce_field('wpacu_settings_update');
            submit_button('Update All Settings');
            ?>
            <div id="wpacu-updating-settings">
                <img src="<?php echo admin_url(); ?>/images/spinner.gif" align="top" width="20" height="20" alt="" />
            </div>
        </div>
        <input type="hidden"
               name="wpacu_selected_tab_area"
               id="wpacu-selected-tab-area"
               value="" />
    </form>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // [START] MODAL INFO
        var currentModal;
        $('.wpacu-modal').each(function (wpacuIndex) {
            var wpacuModalId = $(this).attr('id');

            // Get the modal
            var wpacuModal = document.getElementById(wpacuModalId);

            // Get the button that opens the modal
            var wpacuBtn = document.getElementById(wpacuModalId + '-link');

            //removeIf(development)
            //console.log(wpacuModalId + '-link');
            //endRemoveIf(development)

            // When the user clicks the button, open the modal
            wpacuBtn.onclick = function() {
                wpacuModal.style.display = 'block';
                currentModal = wpacuModal;
            };

            // Get the <span> element that closes the modal
            var wpacuSpan = document.getElementsByClassName('wpacu-close')[wpacuIndex];

            // When the user clicks on <span> (x), close the modal
            wpacuSpan.onclick = function() {
                wpacuModal.style.display = 'none';
            };
        });

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function (event) {
            if (event.target === currentModal) {
                currentModal.style.display = 'none';
            }
        };
        // [END] MODAL INFO
    });

    <?php
    if (! empty($_POST)) {
    ?>
        // Situations: After settings update (post mode), do not jump to URL's anchor
        if (location.hash) {
            setTimeout(function() {
                window.scrollTo(0, 0);
            }, 1);
        }
    <?php
    }
    ?>
</script>