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
    <table class="wp-list-table widefat plugins">
        <thead>
        <tr>
            <td width="100">&nbsp;</td>
            <td>Active Plugin</td>
        <tr>
        </thead>
        <?php
        foreach ($data['active_plugins'] as $plugin) {
            $plugin_data = get_plugin_data(WP_CONTENT_DIR . '/plugins/'.$plugin);
            list($plugin_dir) = explode('/', $plugin);
        ?>
        <tr>
            <td><img width="40" height="40" alt="" src="<?php echo isset($data['plugins_icons'][$plugin_dir]) ? $data['plugins_icons'][$plugin_dir] : ''; ?>" /></td>
            <td><?php echo $plugin_data['Name']; ?></td>
        </tr>
        <?php } ?>
    </table>
</div>