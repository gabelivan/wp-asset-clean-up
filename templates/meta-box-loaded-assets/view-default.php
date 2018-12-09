<?php
// no direct access
if (! isset($data)) {
	exit;
}

$listAreaStatus = $data['plugin_settings']['assets_list_layout_areas_status'];

/*
* --------------------
* [START] STYLES LIST
* --------------------
*/
?>
<div class="wpacu-contract-expand-area">
    <div class="col-left">
        <h4>&#10141; Total enqueued files: <strong><?php echo (int)$data['total_styles'] + (int)$data['total_scripts']; ?></strong></h4>
    </div>
    <div class="col-right">
        <a href="#" id="wpacu-assets-contract-all" class="wpacu-wp-button wpacu-wp-button-secondary">Contract Both Areas</a>&nbsp;
        <a href="#" id="wpacu-assets-expand-all" class="wpacu-wp-button wpacu-wp-button-secondary">Expand Both Areas</a>
    </div>
    <div class="wpacu-clearfix"></div>
</div>

<div class="wpacu-assets-collapsible-wrap wpacu-wrap-styles">
    <a class="wpacu-assets-collapsible <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-assets-collapsible-active<?php } ?>" href="#wpacu-assets-styles-collapsible-content">
        <span class="dashicons dashicons-admin-appearance"></span> &nbsp; <?php _e('Styles (.css files)', WPACU_PLUGIN_NAME); ?> &#10141; Total: <?php echo $data['total_styles']; ?>
    </a>

    <div id="wpacu-assets-styles-collapsible-content"
         class="wpacu-assets-collapsible-content <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-open<?php } ?>">
        <div>
            <?php
            if (! empty($data['all']['styles'])) {
                ?>
                <p><?php echo sprintf(__('The following styles are loading on this page. Please select the ones that are %sNOT NEEDED%s. If you are not sure which ones to unload, it is better to leave them enabled (unchecked) and consult with a developer about unloading the assets.', WPACU_PLUGIN_NAME), '<span style="color: #CC0000;"><strong>', '</strong></span>'); ?></p>
                <p><?php echo __('"Load in on this page (make exception)" will take effect when a bulk unload rule is used. Otherwise, the asset will load anyway unless you select it for unload.', WPACU_PLUGIN_NAME); ?></p>
                <?php
	            if ($data['plugin_settings']['hide_core_files']) {
		            ?>
                    <div class="wpacu_note"><span class="dashicons dashicons-info"></span> WordPress CSS core files are hidden as requested in the plugin's settings. They are meant to be managed by experienced developers in special situations.</div>
                    <div style="clear:both; margin-top: 10px;"></div>
		            <?php
	            }

                if ($data['core_styles_loaded'] && ! $data['plugin_settings']['hide_core_files']) {
                    ?>
                    <div class="wpacu_note wpacu_warning"><em><?php
                            echo sprintf(
                                __('CSS files that are marked with %s are part of WordPress core files. Be careful if you decide to unload them! If you are not sure what to do, just leave them loaded by default and consult with a developer.', WPACU_PLUGIN_NAME),
                                '<span class="dashicons dashicons-warning"></span>'
                            );
                            ?>
                        </em></div><br />
                    <?php
                }
                ?>
                <table class="wpacu_list_table wpacu_widefat wpacu_striped">
                    <tbody>
                    <?php
                    require_once __DIR__.'/_asset-style-rows.php';
                    ?>
                    </tbody>
                </table>
                <?php
            } else {
                echo __('It looks like there are no public .css files loaded or the ones visible do not follow <a href="https://codex.wordpress.org/Function_Reference/wp_enqueue_style">the WordPress way of enqueuing styles</a>.', WPACU_PLUGIN_NAME);
            }
            ?>
        </div>
    </div>
</div>
<?php
/*
* -------------------
* [END] STYLES LIST
* -------------------
*/

/*
 * ---------------------
 * [START] SCRIPTS LIST
 * ---------------------
 */
?>

<div class="wpacu-assets-collapsible-wrap wpacu-wrap-scripts">
    <a class="wpacu-assets-collapsible <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-assets-collapsible-active<?php } ?>" href="#wpacu-assets-scripts-collapsible-content">
        <span class="dashicons dashicons-media-code"></span> &nbsp; <?php _e('Scripts (.js files)', WPACU_PLUGIN_NAME); ?> &#10141; Total: <?php echo $data['total_scripts']; ?>
    </a>

    <div id="wpacu-assets-scripts-collapsible-content"
         class="wpacu-assets-collapsible-content <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-open<?php } ?>">
        <div>
        <?php
        if (! empty($data['all']['scripts'])) {
            ?>
            <p><?php echo sprintf(__('The following scripts are loading on this page. Please select the ones that are %sNOT NEEDED%s. If you are not sure which ones to unload, it is better to leave them enabled and consult with a developer about unloading the assets.', WPACU_PLUGIN_NAME), '<span style="color: #CC0000;"><strong>', '</strong></span>'); ?></p>
            <p><?php echo __('"Load in on this page (make exception)" will take effect when a bulk unload rule is used. Otherwise, the asset will load anyway unless you select it for unload.', WPACU_PLUGIN_NAME); ?></p>
            <?php
            if ($data['plugin_settings']['hide_core_files']) {
                ?>
                <div class="wpacu_note"><span class="dashicons dashicons-info"></span> WordPress JavaScript core files are hidden as requested in the plugin's settings. They are meant to be managed by experienced developers in special situations.</div>
                <div style="clear:both; margin-top: 10px;"></div>
                <?php
            }

            if ($data['core_scripts_loaded'] && ! $data['plugin_settings']['hide_core_files']) {
                ?>
                <div class="wpacu_note wpacu_warning"><em><?php
                        echo sprintf(
                            __('JavaScript files that are marked with %s are part of WordPress core files. Be careful if you decide to unload them! If you are not sure what to do, just leave them loaded by default and consult with a developer.', WPACU_PLUGIN_NAME),
                            '<span class="dashicons dashicons-warning"></span>'
                        );
                        ?>
                    </em></div><br />
                <?php
            }
            ?>

            <table class="wpacu_list_table wpacu_widefat wpacu_striped">
                <tbody>
                <?php
                require_once __DIR__.'/_asset-script-rows.php';
                ?>
                </tbody>
            </table>
            <?php
        } else {
            echo __('It looks like there are no public .js files loaded or the ones visible do not follow <a href="https://codex.wordpress.org/Function_Reference/wp_enqueue_script">the WordPress way of enqueuing scripts</a>.', WPACU_PLUGIN_NAME);
        }
        ?>
        </div>
    </div>
</div>
<?php
include '_inline_js.php';
/*
 * -------------------
 * [END] SCRIPTS LIST
 * -------------------
 */
