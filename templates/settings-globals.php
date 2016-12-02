<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}
?>
<h1><?php _e('WP Asset CleanUp', WPACU_PLUGIN_NAME); ?></h1>
<h2><?php _e('Global Rules', WPACU_PLUGIN_NAME); ?></h2>

<nav class="nav-tab-wrapper">
    <a href="admin.php?page=wpassetcleanup_globals" class="nav-tab <?php if ($data['for'] === 'everywhere') { ?>nav-tab-active<?php } ?>">Everywhere</a>
    <a href="admin.php?page=wpassetcleanup_globals&wpacu_for=post_types" class="nav-tab <?php if ($data['for'] === 'post_types') { ?>nav-tab-active<?php } ?>">Post Types</a>
</nav>

<div class="clear"></div>

<?php
if ($data['for'] === 'post_types') {
    ?>
    <div style="margin: 15px 0;">
        <form id="wpacu_post_type_form" method="get" action="admin.php">
            <input type="hidden" name="page" value="wpassetcleanup_globals" />
            <input type="hidden" name="wpacu_for" value="post_types" />

            <div style="margin: 0 0 10px 0;">Select the post type for which you want to see the unloaded scripts &amp; styles:</div>
            <select id="wpacu_post_type_select" name="wpacu_post_type">
                <?php foreach ($data['post_types_list'] as $postType) { ?>
                <option <?php if ($data['post_type'] === $postType) { echo 'selected="selected"'; } ?> value="<?php echo $postType; ?>"><?php echo $postType; ?></option>
                <?php } ?>
            </select>
        </form>
    </div>
    <?php
}
?>

<form action="" method="post">
<?php
if ($data['for'] === 'everywhere') {
    ?>
    <div class="clear"></div>

    <div class="alert">
        <p>This is the list of the assets that are <strong>globally unloaded</strong> on all pages (including home page).</p>
        <p>If you want to remove this rule and have them loading, use the "Remove rule" checkbox.</p>
        <p style="margin: 0; background: white; padding: 10px; border: 1px solid #ccc; width: auto; display: inline-block;">This list fills once you select "<em>Unload everywhere</em>" when you edit posts/pages for the assets that you want to prevent from loading on every page.</p>
    </div>

    <div class="clear"></div>

    <div style="padding: 0 10px 0 0;">
        <h3>Styles</h3>
        <?php
        if (! empty($data['values']['styles'])) {
            ?>
            <table class="wp-list-table widefat fixed striped">
                <tr>
                    <td><strong>Handle</strong></td>
                    <td><strong>Actions</strong></td>
                </tr>
                <?php
                foreach ($data['values']['styles'] as $handle) {
                    ?>
                    <tr class="wpacu_global_rule_row">
                        <td><strong><span style="color: green;"><?php echo $handle; ?></span></strong></td>
                        <td>
                            <label><input type="checkbox"
                                          class="wpacu_remove_rule"
                                          name="wpacu_options_styles[<?php echo $handle; ?>]"
                                          value="remove" /> Remove rule</label>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
        } else {
            ?>
            <p>There are no unloaded styles for your selection.</p>
            <?php
        }
        ?>

        <h3>Scripts</h3>
        <?php
        if (! empty($data['values']['scripts'])) {
            ?>
            <table class="wp-list-table widefat fixed striped">
                <tr>
                    <td><strong>Handle</strong></td>
                    <td><strong>Actions</strong></td>
                </tr>
                <?php
                foreach ($data['values']['scripts'] as $handle) {
                    ?>
                    <tr class="wpacu_global_rule_row">
                        <td><strong><span style="color: green;"><?php echo $handle; ?></span></strong></td>
                        <td>
                            <label><input type="checkbox"
                                          class="wpacu_remove_rule"
                                          name="wpacu_options_scripts[<?php echo $handle; ?>]"
                                          value="remove" /> Remove rule</label>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
        } else {
            ?>
            <p>There are no unloaded scripts for your selection.</p>
            <?php
        }
        ?>
    </div>
    <?php
} elseif ($data['for'] === 'post_types') {
    ?>
    <div class="clear"></div>

    <div class="alert">
        <p>This is the list of the assets that are <strong>unloaded</strong> on all pages belonging to the <strong><u><?php echo $data['post_type']; ?></u></strong> post type.</p>
        <p>If you want to remove this rule, use the "Remove rule" checkbox.</p>
        <p style="margin: 0; background: white; padding: 10px; border: 1px solid #ccc; width: auto; display: inline-block;">This list fills once you select "<em>Unload on All Pages of <strong><?php echo $data['post_type']; ?></strong> post type</em>" when you edit posts/pages for the assets that you want to prevent from loading.</p>
    </div>

    <div class="clear"></div>

    <div style="padding: 0 10px 0 0;">
        <h3>Styles</h3>
        <?php
        if (! empty($data['values']['styles'])) {
            ?>
            <table class="wp-list-table widefat fixed striped">
                <tr>
                    <td><strong>Handle</strong></td>
                    <td><strong>Actions</strong></td>
                </tr>
                <?php
                foreach ($data['values']['styles'] as $handle) {
                    ?>
                    <tr class="wpacu_global_rule_row">
                        <td><strong><span style="color: green;"><?php echo $handle; ?></span></strong></td>
                        <td>
                            <label><input type="checkbox"
                                          class="wpacu_remove_rule"
                                          name="wpacu_options_post_type_styles[<?php echo $handle; ?>]"
                                          value="remove" /> Remove rule</label>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
        } else {
            ?>
            <p>There are no unloaded styles for your selection.</p>
            <?php
        }
        ?>

        <h3>Scripts</h3>
        <?php
        if (! empty($data['values']['scripts'])) {
            ?>
            <table class="wp-list-table widefat fixed striped">
                <tr>
                    <td><strong>Handle</strong></td>
                    <td><strong>Actions</strong></td>
                </tr>
                <?php
                foreach ($data['values']['scripts'] as $handle) {
                    ?>
                    <tr class="wpacu_global_rule_row">
                        <td><strong><span style="color: green;"><?php echo $handle; ?></span></strong></td>
                        <td>
                            <label><input type="checkbox"
                                          class="wpacu_remove_rule"
                                          name="wpacu_options_post_type_scripts[<?php echo $handle; ?>]"
                                          value="remove" /> Remove rule</label>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
        } else {
            ?>
            <p>There are no unloaded scripts for your selection.</p>
            <?php
        }
        ?>
    </div>
    <?php
}

$noAssetsToRemove = (empty($data['values']['styles']) && empty($data['values']['scripts']));
?>
<?php wp_nonce_field($data['nonce_action'], $data['nonce_name']); ?>

<input type="hidden" name="wpacu_for" value="<?php echo $data['for']; ?>" />
<input type="hidden" name="wpacu_update" value="1" />

    <?php
    if ($data['post_type']) {
    ?>
    <input type="hidden" name="wpacu_post_type" value="<?php echo $data['post_type']; ?>" />
    <?php
    }
    ?>

<div class="clear"></div>

<p class="submit">
    <input type="submit"
           name="submit"
           id="submit"
           <?php if ($noAssetsToRemove) { ?>
           disabled="disabled"
           <?php } ?>
           class="button button-primary"
           value="<?php esc_attr_e('Update', WPACU_PLUGIN_NAME); ?>" />
    <?php
    if ($noAssetsToRemove) {
        ?>
        &nbsp;<small>Note: There are no unloaded assets (scripts &amp; styles) to be managed</small>
        <?php
    }
    ?>
</p>
</form>