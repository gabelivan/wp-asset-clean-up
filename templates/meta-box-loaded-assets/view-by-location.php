<?php
// no direct access
if (! isset($data)) {
	exit;
}

// Show areas by:
// "Plugins", "Themes" (parent theme and child theme), "WordPress Core"
// External locations (outside plugins and themes)
// 3rd party external locations (e.g. Google API Fonts, CND urls such as the ones for Bootstrap etc.)
$listAreaStatus = $data['plugin_settings']['assets_list_layout_areas_status'];

/*
* -------------------------
* [START] BY EACH LOCATION
* -------------------------
*/
?>

<div>
    <?php
    if (! empty($data['all']['styles']) || ! empty($data['all']['scripts'])) {
    ?>
    <p><?php echo sprintf(__('The following styles &amp; scripts are loading on this page. Please select the ones that are %sNOT NEEDED%s. If you are not sure which ones to unload, it is better to leave them enabled and consult with a developer about unloading the assets.', WPACU_PLUGIN_TEXT_DOMAIN), '<span style="color: #CC0000;"><strong>', '</strong></span>'); ?></p>
    <p><?php echo __('"Load in on this page (make exception)" will take effect when a bulk unload rule is used. Otherwise, the asset will load anyway unless you select it for unload.', WPACU_PLUGIN_TEXT_DOMAIN); ?></p>
    <?php
    if ($data['plugin_settings']['hide_core_files']) {
        ?>
        <div class="wpacu_note"><span class="dashicons dashicons-info"></span> WordPress CSS &amp; JavaScript core files are hidden as requested in the plugin's settings. They are meant to be managed by experienced developers in special situations.</div>
        <div class="wpacu-clearfix" style="margin-top: 10px;"></div>
        <?php
    }

    if (($data['core_styles_loaded'] || $data['core_scripts_loaded']) && ! $data['plugin_settings']['hide_core_files']) {
        ?>
        <div class="wpacu_note wpacu_warning"><em><?php
                echo sprintf(
                    __('Assets that are marked with %s are part of WordPress core files. Be careful if you decide to unload them! If you are not sure what to do, just leave them loaded by default and consult with a developer.', WPACU_PLUGIN_TEXT_DOMAIN),
                    '<span class="dashicons dashicons-warning"></span>'
                );
                ?>
            </em></div><br />
        <?php
    }
    ?>
</div>
        <?php
	    $allPlugins = get_plugins();
	    $allThemes  = wp_get_themes();

	    $allActivePluginsIcons = \WpAssetCleanUp\Misc::fetchActivePluginsIcons(true);

	    $locationsText = array(
            'plugins'  => '<span class="dashicons dashicons-admin-plugins"></span> From Plugins (.css &amp; .js)',
            'themes'   => '<span class="dashicons dashicons-admin-appearance"></span> From Themes (.css &amp; .js)',
            'wp_core'  => '<span class="dashicons dashicons-wordpress"></span> WordPress Core (.css &amp; .js)',
            'external' => '<span class="dashicons dashicons-cloud"></span> External (.css &amp; .js)'
        );

	    $data['view_by_location'] =
        $data['rows_build_array'] =
        $data['rows_by_location'] = true;

        $data['rows_assets'] = array();

        require_once __DIR__.'/_asset-style-rows.php';
        require_once __DIR__.'/_asset-script-rows.php';

        if (! empty($data['rows_assets'])) {
            // Sorting: Plugins, Themes and External Assets
            $rowsAssets = array('plugins' => array(), 'themes' => array(), 'external' => array());

	        foreach ($data['rows_assets'] as $locationMain => $values) {
		        $rowsAssets[$locationMain] = $values;
	        }

            foreach ($rowsAssets as $locationMain => $values) {
                ksort($values);
                ?>
                <div <?php if ($locationMain === 'wp_core' && $data['plugin_settings']['hide_core_files']) { echo 'style="display: none;"'; } ?> class="wpacu-assets-collapsible-wrap wpacu-by-location wpacu-<?php echo $locationMain; ?>">
                    <a class="wpacu-assets-collapsible <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-assets-collapsible-active<?php } ?>" href="#wpacu-assets-collapsible-content-<?php echo $locationMain; ?>">
                        <?php _e($locationsText[$locationMain], WPACU_PLUGIN_TEXT_DOMAIN); ?>
                    </a>

                    <div class="wpacu-assets-collapsible-content <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-open<?php } ?>">
                        <?php if ($locationMain === 'external') { ?>
                            <p class="wpacu-assets-note"><strong>Note:</strong> External .css and .js assets are considered those who are hosted on a different domain (e.g. Google Font API, assets loaded from external CDNs) and the ones outside the "plugins" (usually /wp-content/plugins/) and the "themes" (usually /wp-content/themes/) directories.</p>
                        <?php } elseif ($locationMain === 'wp_core' && ! $data['plugin_settings']['hide_core_files']) { ?>
                            <p class="wpacu-assets-note"><strong>Note:</strong> Please be careful when doing any changes to the following core assets as they can break the functionality of the front-end website. If you're not sure about unloading any asset, just leave it loaded.</p>
                        <?php } ?>

                        <?php foreach ($values as $locationChild => $values2) { ?>
                            <?php if ($locationChild !== 'none') {
                                if ($locationMain === 'plugins') {
                                    $locationChildText = \WpAssetCleanUp\Info::getPluginInfo($locationChild, $allPlugins, $allActivePluginsIcons);
                                } elseif ($locationMain === 'themes') {
			                        $locationChildText = \WpAssetCleanUp\Info::getThemeInfo($locationChild, $allThemes);
		                        } else {
	                                $locationChildText = $locationChild;
                                }
                                ?>
                                <div class="wpacu-location-child-area">
                                    <strong><?php echo $locationChildText; ?></strong>
                                </div>
                            <?php } ?>
                        <table class="wpacu_list_table wpacu_list_by_location wpacu_widefat wpacu_striped">
                            <tbody>
                            <?php
                            ksort($values2);

                            foreach ($values2 as $assetType => $assetRows) {
                                foreach ($assetRows as $assetRow) {
	                                echo $assetRow . "\n";
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    <?php } ?>
                    </div>
                </div>
                <?php
            }
        }
    }
/*
* -----------------------
* [END] BY EACH LOCATION
* -----------------------
*/

include '_inline_js.php';
