<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
	exit;
}

include_once '_top-area.php';
?>

<div class="wpacu-wrap">
    <nav class="nav-tab-wrapper">
        <a href="<?php echo admin_url('admin.php?page=wpassetcleanup_assets_manager&wpacu_for=homepage'); ?>" class="nav-tab <?php if ($data['for'] === 'homepage') { ?>nav-tab-active<?php } ?>">Homepage</a>
        <a href="<?php echo admin_url('admin.php?page=wpassetcleanup_assets_manager&wpacu_for=all_other_pages'); ?>" class="nav-tab <?php if ($data['for'] === 'all_other_pages') { ?>nav-tab-active<?php } ?>">All Other Pages</a>
    </nav>

    <div class="wpacu-clearfix"></div>

    <?php
    if ($data['for'] === 'homepage') {
        include_once 'admin-page-child-settings-homepage.php';
    } elseif ($data['for'] === 'all_other_pages') {
	    include_once 'admin-page-child-pages-info.php';
    }
    ?>
</div>