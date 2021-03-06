<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
	exit;
}

$wpacu_top_area_links = array(
    'admin.php?page=wpassetcleanup_settings' => array(
        'icon' => '<span class="dashicons dashicons-admin-generic"></span>',
        'title' => 'Settings',
        'page' => 'wpassetcleanup_settings'
    ),
    'admin.php?page=wpassetcleanup_assets_manager'    => array(
        'icon' => '<span class="dashicons dashicons-media-code"></span>',
	    'title' => 'CSS &amp; JavaScript Load Manager',
	    'page'  => 'wpassetcleanup_assets_manager',
    ),
    //removeIf(development)
    /*
    'admin.php?page=wpassetcleanup_plugins_manager'    => array(
	    'icon' => '<span class="dashicons dashicons-admin-plugins"></span>',
	    'title' => 'Plugins Unloader',
	    'page'  => 'wpassetcleanup_plugins_manager',
    ),
    */
	//endRemoveIf(development)
    'admin.php?page=wpassetcleanup_bulk_unloads' => array(
	    'icon' => '<span class="dashicons dashicons-networking"></span>',
	    'title' => 'Bulk Unloads',
	    'page'  => 'wpassetcleanup_bulk_unloads'
    ),
    'admin.php?page=wpassetcleanup_tools' => array(
	    'icon' => '<span class="dashicons dashicons-admin-tools"></span>',
	    'title' => 'Tools',
	    'page' => 'wpassetcleanup_tools'
    ),
    'admin.php?page=wpassetcleanup_license' => array(
        'icon' => '<span class="dashicons dashicons-awards"></span>',
        'title' => 'License',
        'page' => 'wpassetcleanup_license'
    ),
    'admin.php?page=wpassetcleanup_get_help' => array(
        'icon' => '<span class="dashicons dashicons-sos"></span>',
        'title' => 'Help',
        'page' => 'wpassetcleanup_get_help'
    ),
    // [wpacu_lite]
    'admin.php?page=wpassetcleanup_go_pro' => array(
	    'icon' => '<span class="dashicons dashicons-star-filled"></span>',
	    'title' => 'Go Pro',
	    'page' => 'wpassetcleanup_go_pro',
        'target' => '_blank'
    )
	// [/wpacu_lite]
);

global $current_screen;

$wpacu_current_page = str_replace(array('asset-cleanup_page_', 'toplevel_page_'), '', $current_screen->base);
?>
<div id="wpacu-logo-area">
    <img alt="" src="<?php echo WPACU_PLUGIN_URL; ?>/assets/images/asset-cleanup-logo.png" />
</div>

<div class="wpacu-tabs wpacu-tabs-style-topline">
	<nav>
		<ul>
            <?php foreach ($wpacu_top_area_links as $wpacu_link => $wpacu_info) { ?>
			<li <?php if ($wpacu_current_page === $wpacu_info['page']) { echo 'class="wpacu-tab-current"'; } ?>>
                <a <?php if (isset($wpacu_info['target']) && $wpacu_info['target'] === '_blank') { ?>target="_blank"<?php } ?> href="<?php echo $wpacu_link; ?>"><?php echo $wpacu_info['icon']; ?> <span><?php echo $wpacu_info['title']; ?></span></a>
            </li>
            <?php } ?>
		</ul>
	</nav>
</div><!-- /tabs -->