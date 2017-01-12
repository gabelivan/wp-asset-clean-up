<?php
/*
 * No direct access to this file
 * This content is placed inside #wpacu_meta_box_content meta box DIV element
 */
if (! isset($data)) {
    exit;
}
?>
<div class="wpacu_verified">
    <strong>Verified Page:</strong> <a target="_blank" href="<?php echo $data['fetch_url']; ?>"><span><?php echo $data['fetch_url']; ?></span></a>
</div>
<?php
/*
 * --------------------
 * [START] STYLES LIST
 * --------------------
 */
?>
<h3><?php _e('Styles (.css files)', WPACU_PLUGIN_NAME); ?></h3>
<?php
if (! empty($data['all']['styles'])) {
    ?>
    <p><?php echo sprintf(__('The following styles are loading on this page. Please select the ones that are %sNOT NEEDED%s. If you are not sure which ones to unload, it is better to leave them enabled (unchecked) and consult with a developer about unloading the assets.', WPACU_PLUGIN_NAME), '<span style="color: #CC0000;"><strong>', '</strong></span>'); ?></p>
    <p><?php echo __('"Load in on this page (make exception)" will take effect when a bulk unload rule is used. Otherwise, the asset will load anyway unless you select it for unload.', WPACU_PLUGIN_NAME); ?></p>
    <?php
    if ($data['core_styles_loaded']) {
        ?>
        <div class="wpacu_note wpacu_warning"><em><?php
                echo sprintf(
                    __('Assets that are marked with %s are part of WordPress core files. Be careful if you decide to unload them!', WPACU_PLUGIN_NAME),
                    '<img src="'.WPACU_PLUGIN_URL.'/assets/img/icon-warning.png" width="20" height="20" alt="" align="top" />'
                );
                ?>
            </em></div><br />
        <?php
    }
    ?>

    <table class="wp-asset-clean-up wp-list-table widefat wpacu_widefat fixed striped wpacu_striped">
        <tbody>
        <?php
        foreach ($data['all']['styles'] as $obj) {
            $active = (isset($data['current']['styles']) && in_array($obj->handle, $data['current']['styles']));

            $class = ($active) ? 'wpacu_not_load' : '';
            $checked = ($active) ? 'checked="checked"' : '';

            $globalUnloaded = $postTypeUnloaded = $isLoadException = $isGlobalRule = false;

            // Mark it as unloaded - Everywhere
            if (in_array($obj->handle, $data['global_unload']['styles'])) {
                $globalUnloaded = $isGlobalRule = true;
            }

            // Mark it as unloaded - for the Current Post Type
            if (isset($data['post_type_unloaded']['styles']) && in_array($obj->handle, $data['post_type_unloaded']['styles'])) {
                $postTypeUnloaded = $isGlobalRule = true;
            }

            if ($isGlobalRule) {
                if (in_array($obj->handle, $data['load_exceptions']['styles'])) {
                    $isLoadException = true;
                } else {
                    $class .= ' wpacu_not_load';
                }
            }
            ?>
            <tr class="wpacu_asset_row <?php echo $class; ?>">
                <td scope="row" class="wpacu_check check-column" valign="top"><input id="style_<?php echo $obj->handle; ?>" <?php if ($globalUnloaded) { echo 'disabled="disabled"'; } echo $checked; ?> name="<?php echo WPACU_PLUGIN_NAME; ?>[styles][]" class="icheckbox_square-red" type="checkbox" value="<?php echo $obj->handle; ?>" /></td>
                <td valign="top" style="width: 100%;">
                    <p style="margin-top: 0px;">
                        <label for="style_<?php echo $obj->handle; ?>"><?php _e('Handle:', WPACU_PLUGIN_NAME); ?> <strong><span style="color: green;"><?php echo $obj->handle; ?></span></strong></label>
                        <?php
                        if (isset($obj->wp) && $obj->wp) {
                            ?>
                            <img src="<?php echo WPACU_PLUGIN_URL; ?>/assets/img/icon-warning.png"
                                 width="20"
                                 height="20"
                                 alt=""
                                 align="top" />
                            <?php
                        }
                        ?>
                    </p>

                    <div style="padding: 5px 10px; margin: 15px 0; background: white; border: 1px solid #eee; border-radius: 5px;">
                    <?php
                    // Unloaded Everywhere
                    if ($globalUnloaded) {
                    ?>
                        <p><strong style="color: #d54e21;">This asset is unloaded everywhere</strong></p>
                        <div class="clear"></div>
                        <?php
                    }
                    ?>

                    <ul class="wpacu_asset_options">
                    <?php
                    // [START] UNLOAD EVERYWHERE
                    if ($globalUnloaded) {
                    ?>
                            <li>
                                <label><input data-handle="<?php echo $obj->handle; ?>"
                                          class="wpacu_global_option wpacu_style"
                                          type="radio"
                                          name="wpacu_options_styles[<?php echo $obj->handle; ?>]"
                                          checked="checked"
                                          value="default" />
                                Keep the unload global rule</label>
                            </li>

                            <li>
                                <label><input data-handle="<?php echo $obj->handle; ?>"
                                          class="wpacu_global_option wpacu_style"
                                          type="radio"
                                          name="wpacu_options_styles[<?php echo $obj->handle; ?>]"
                                          value="remove" />
                                Remove global unload rule</label>
                            </li>
                    <?php
                    } else {
                        ?>
                            <li>
                                <label><input data-handle="<?php echo $obj->handle; ?>"
                                              class="wpacu_global_unload wpacu_global_style"
                                              id="wpacu_global_unload_style_<?php echo $obj->handle; ?>" type="checkbox"
                                              name="wpacu_global_unload_styles[]" value="<?php echo $obj->handle; ?>"/>
                                    Unload Everywhere</label>
                            </li>
                        <?php
                    }
                    // [END] UNLOAD EVERYWHERE
                    ?>
                        </ul>
                    </div>

                    <?php if ($data['post_type']) { ?>
                    <div style="padding: 5px 10px; margin: 15px 0; background: white; border: 1px solid #eee; border-radius: 5px;">
                    <?php } ?>

                    <?php
                    // Unloaded On All Pages Belonging to the page's Post Type
                    if ($postTypeUnloaded) {
                        ?>
                        <p><strong style="color: #d54e21;">This asset is unloaded on all <u><?php echo $data['post_type']; ?></u> post types.</strong></p>
                        <div class="clear"></div>
                        <?php
                    }
                    ?>

                    <ul class="wpacu_asset_options">
                    <?php
                    if ($data['post_type']) {
                        // [START] ALL PAGES HAVING THE SAME POST TYPE
                        if ($postTypeUnloaded) {
                            ?>
                            <li>
                                <label><input data-handle="<?php echo $obj->handle; ?>"
                                              class="wpacu_bulk_option wpacu_style"
                                              type="radio"
                                              name="wpacu_options_post_type_styles[<?php echo $obj->handle; ?>]"
                                              checked="checked"
                                              value="default"/>
                                    Keep rule</label>
                            </li>

                            <li>
                                <label><input data-handle="<?php echo $obj->handle; ?>"
                                              class="wpacu_bulk_option wpacu_style"
                                              type="radio"
                                              name="wpacu_options_post_type_styles[<?php echo $obj->handle; ?>]"
                                              value="remove"/>
                                    Remove rule</label>
                            </li>
                            <?php
                        } else {
                            ?>
                            <li>
                                <label><input data-handle="<?php echo $obj->handle; ?>"
                                              class="wpacu_post_type_unload wpacu_post_type_style"
                                              id="wpacu_bulk_unload_post_type_style_<?php echo $obj->handle; ?>"
                                              type="checkbox"
                                              name="wpacu_bulk_unload_styles[post_type][<?php echo $data['post_type']; ?>][]"
                                              value="<?php echo $obj->handle; ?>"/>
                                    Unload on All Pages of <strong><?php echo $data['post_type']; ?></strong> post type</label>
                            </li>
                            <?php
                        }
                    }
                    // [END] ALL PAGES HAVING THE SAME POST TYPE
                    ?>
                        </ul>

                    <?php if ($data['post_type']) { ?>
                    </div>
                    <?php } ?>

                    <ul class="wpacu_asset_options">
                        <li id="wpacu_load_it_option_style_<?php echo $obj->handle; ?>">
                            <label><input data-handle="<?php echo $obj->handle; ?>"
                                          id="wpacu_style_load_it_<?php echo $obj->handle; ?>"
                                          class="wpacu_load_it_option wpacu_style wpacu_load_exception"
                                          type="checkbox"
                                    <?php if ($isLoadException) { ?> checked="checked" <?php } ?>
                                          name="wpacu_styles_load_it[]"
                                          value="<?php echo $obj->handle; ?>"/>
                                Load it on this page (make exception<?php if (! $isGlobalRule) { echo ' * works only IF any of two rules above are selected'; } ?>)</label>
                        </li>
                    </ul>
                    <?php
                    if (isset($obj->src) && $obj->src != '') {
                        ?>
                        <p><?php _e('Source:', WPACU_PLUGIN_NAME); ?> <a target="_blank" href="<?php echo $obj->srcHref; ?>"><?php echo $obj->src; ?></a></p>
                    <?php }

                    if (! empty($obj->deps)) {
                    ?>
                    <p><?php echo __('Depends on:', WPACU_PLUGIN_NAME) . ' ' . implode(', ', $obj->deps); ?></p>
                    <?php
                    }

                    if ($obj->ver) {
                        ?>
                        <p><?php _e('Version:', WPACU_PLUGIN_NAME); ?> <?php echo $obj->ver; ?></p>
                        <?php
                    }

                    if (isset($obj->extra->data) && ! empty($obj->extra->data)) { ?>
                        <p><?php _e('Inline:', WPACU_PLUGIN_NAME); ?> <em><?php echo $obj->extra->data; ?></em></p>
                    <?php } ?>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <?php
} else {
    echo __('It looks like there are no public .css files loaded or the ones visible do not follow <a href="https://codex.wordpress.org/Function_Reference/wp_enqueue_style">the WordPress way of enqueuing styles</a>.', WPACU_PLUGIN_NAME);
}
/* -------------------
 * [END] STYLES LIST
 * -------------------
 */

/*
 * ---------------------
 * [START] SCRIPTS LIST
 * ---------------------
 */
?>
<h3><?php _e('Scripts (.js files)', WPACU_PLUGIN_NAME); ?></h3>
<?php
if (! empty($data['all']['scripts'])) {
    ?>
    <p><?php echo sprintf(__('The following scripts are loading on this page. Please select the ones that are %sNOT NEEDED%s. If you are not sure which ones to unload, it is better to leave them enabled and consult with a developer about unloading the assets.', WPACU_PLUGIN_NAME), '<span style="color: #CC0000;"><strong>', '</strong></span>'); ?></p>
    <p><?php echo __('"Load in on this page (make exception)" will take effect when a bulk unload rule is used. Otherwise, the asset will load anyway unless you select it for unload.', WPACU_PLUGIN_NAME); ?></p>
    <?php
    if ($data['core_scripts_loaded']) {
    ?>
    <div class="wpacu_note wpacu_warning"><em><?php
        echo sprintf(
            __('Assets that are marked with %s are part of WordPress core files. Be careful if you decide to unload them!', WPACU_PLUGIN_NAME),
            '<img src="'.WPACU_PLUGIN_URL.'/assets/img/icon-warning.png" width="20" height="20" alt="" align="top" />'
        );
        ?>
    </em></div><br />
    <?php
    }
    ?>

    <table class="wp-list-table widefat wpacu_widefat fixed striped wpacu_striped">
        <tbody>
        <?php
        foreach ($data['all']['scripts'] as $obj) {
            $active = (isset($data['current']['scripts']) && in_array($obj->handle, $data['current']['scripts']));

            $class = ($active) ? 'wpacu_not_load' : '';
            $checked = ($active) ? 'checked="checked"' : '';

            $globalUnloaded = $postTypeUnloaded = $isLoadException = $isGlobalRule = false;

            // Mark it as unloaded - Everywhere
            if (in_array($obj->handle, $data['global_unload']['scripts']) && !$class) {
                $globalUnloaded = $isGlobalRule = true;
            }

            // Mark it as unloaded - for the Current Post Type
            if (isset($data['post_type_unloaded']['scripts']) && in_array($obj->handle, $data['post_type_unloaded']['scripts']) && !$class) {
                $postTypeUnloaded = $isGlobalRule = true;
            }

            if ($isGlobalRule) {
                if (in_array($obj->handle, $data['load_exceptions']['scripts'])) {
                    $isLoadException = true;
                } else {
                    $class .= ' wpacu_not_load';
                }
            }
            ?>
            <tr class="wpacu_asset_row <?php echo $class; ?>">
                <td scope="row" class="wpacu_check check-column" valign="top"><input id="script_<?php echo $obj->handle; ?>" <?php if ($globalUnloaded) { echo 'disabled="disabled"'; } echo $checked; ?> name="<?php echo WPACU_PLUGIN_NAME; ?>[scripts][]" class="icheckbox_square-red" type="checkbox" value="<?php echo $obj->handle; ?>" /></td>
                <td valign="top" style="width: 100%;">
                    <p style="margin-top: 0px;">
                        <label for="script_<?php echo $obj->handle; ?>"> <?php _e('Handle:', WPACU_PLUGIN_NAME); ?> <strong><span style="color: green;"><?php echo $obj->handle; ?></span></strong></label>
                        <?php
                        if (isset($obj->wp) && $obj->wp) {
                            ?>
                            <img src="<?php echo WPACU_PLUGIN_URL; ?>/assets/img/icon-warning.png"
                                 width="20"
                                 height="20"
                                 alt=""
                                 align="top" />
                            <?php
                        }
                        ?>
                    </p>

                    <?php
                    // Unloaded Everywhere
                    if ($globalUnloaded) {
                    ?>
                        <p><strong style="color: #d54e21;">This asset is unloaded everywhere</strong></p>
                    <?php
                    }
                    ?>

                    <div style="padding: 5px 10px; margin: 15px 0; background: white; border: 1px solid #eee; border-radius: 5px;">
                    <ul class="wpacu_asset_options">

                    <?php
                    // [START] UNLOAD EVERYWHERE
                    if ($globalUnloaded) {
                    ?>
                            <li>
                            <label><input data-handle="<?php echo $obj->handle; ?>"
                                          class="wpacu_bulk_option wpacu_script"
                                          type="radio"
                                          name="wpacu_options_scripts[<?php echo $obj->handle; ?>]"
                                          checked="checked"
                                          value="default" />
                                Keep the unload global rule</label>
                            </li>

                            <li>
                            <label><input data-handle="<?php echo $obj->handle; ?>"
                                          class="wpacu_bulk_option wpacu_script"
                                          type="radio"
                                          name="wpacu_options_scripts[<?php echo $obj->handle; ?>]"
                                          value="remove" />
                                Remove global unload rule</label>
                            </li>
                        <?php
                    } else {
                        ?>
                        <li>
                            <label><input data-handle="<?php echo $obj->handle; ?>"
                                          class="wpacu_global_unload wpacu_global_script"
                                          id="wpacu_global_unload_script_<?php echo $obj->handle; ?>"
                                          type="checkbox"
                                          name="wpacu_global_unload_scripts[]"
                                          value="<?php echo $obj->handle; ?>"/>
                                Unload Everywhere</label>
                        </li>
                    <?php
                    }
                    // [END] UNLOAD EVERYWHERE
                    ?>

                        </ul>
                        </div>

                    <?php if ($data['post_type']) { ?>
                    <div style="padding: 5px 10px; margin: 15px 0; background: white; border: 1px solid #eee; border-radius: 5px;">
                    <?php } ?>

                        <?php
                        // Unloaded On All Pages Belonging to the page's Post Type
                        if ($postTypeUnloaded) {
                            ?>
                            <p><strong style="color: #d54e21;">This asset is unloaded on all <u><?php echo $data['post_type']; ?></u> post types.</strong></p>
                            <div class="clear"></div>
                            <?php
                        }
                        ?>

                        <ul class="wpacu_asset_options">
                    <?php
                    if ($data['post_type']) {
                        // [START] ALL PAGES HAVING THE SAME POST TYPE
                        if ($postTypeUnloaded) {
                            ?>
                            <li>
                                <label><input data-handle="<?php echo $obj->handle; ?>"
                                              class="wpacu_post_type_option wpacu_post_type_script"
                                              type="radio"
                                              name="wpacu_options_post_type_scripts[<?php echo $obj->handle; ?>]"
                                              checked="checked"
                                              value="default"/>
                                    Keep rule</label>
                            </li>

                            <li>
                                <label><input data-handle="<?php echo $obj->handle; ?>"
                                              class="wpacu_post_type_option wpacu_post_type_script"
                                              type="radio"
                                              name="wpacu_options_post_type_scripts[<?php echo $obj->handle; ?>]"
                                              value="remove"/>
                                    Remove rule</label>
                            </li>
                            <?php
                        } else {
                            ?>
                            <li>
                                <label><input data-handle="<?php echo $obj->handle; ?>"
                                              class="wpacu_post_type_unload wpacu_post_type_script"
                                              id="wpacu_global_unload_post_type_script_<?php echo $obj->handle; ?>"
                                              type="checkbox"
                                              name="wpacu_bulk_unload_scripts[post_type][<?php echo $data['post_type']; ?>][]"
                                              value="<?php echo $obj->handle; ?>"/>
                                    Unload on All Pages of <strong><?php echo $data['post_type']; ?></strong> post type</label>
                            </li>
                            <?php
                        }
                    }
                    // [END] ALL PAGES HAVING THE SAME POST TYPE
                    ?>
                            </ul>
                    <?php if ($data['post_type']) { ?>
                    </div>
                    <?php } ?>

                    <ul class="wpacu_asset_options">
                        <li id="wpacu_load_it_option_script_<?php echo $obj->handle; ?>">
                            <label><input data-handle="<?php echo $obj->handle; ?>"
                                          id="wpacu_script_load_it_<?php echo $obj->handle; ?>"
                                          class="wpacu_load_it_option wpacu_script wpacu_load_exception"
                                          type="checkbox"
                                          name="wpacu_scripts_load_it[]"
                                    <?php if ($isLoadException) { ?> checked="checked" <?php } ?>
                                          value="<?php echo $obj->handle; ?>" />
                                Load it on this page (make exception<?php if (! $isGlobalRule) { echo ' * works only IF any of two rules above are selected'; } ?>)</label>
                        </li>
                    </ul>
                    <?php
                    if (isset($obj->src) && $obj->src != '') {
                    ?>
                        <p><?php _e('Source:', WPACU_PLUGIN_NAME); ?> <a target="_blank" href="<?php echo $obj->srcHref; ?>"><?php echo $obj->src; ?></a></p>
                    <?php } ?>

                    <?php
                    if (! empty($obj->deps)) {
                        ?>
                        <p><?php echo __('Depends on:', WPACU_PLUGIN_NAME) . ' ' . implode(', ', $obj->deps); ?></p>
                        <?php
                    }

                    if (isset($obj->ver) && $obj->ver != '') {
                    ?>
                        <p><?php _e('Version:', WPACU_PLUGIN_NAME); ?> <?php echo $obj->ver; ?></p>
                    <?php
                    }

                    if (isset($obj->extra->data) && ! empty($obj->extra->data)) { ?>
                        <p><?php _e('Inline:', WPACU_PLUGIN_NAME); ?> <em><?php echo strip_tags($obj->extra->data); ?></em></p>
                    <?php
                    }

                    if (isset($obj->position) && $obj->position != '') {
                        ?>
                        <p><?php _e('Position:', WPACU_PLUGIN_NAME); ?> <?php echo ($obj->position == 'head') ? 'HEAD' : 'BODY'; ?></p>
                        <?php
                    }
                    ?>
                    </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <?php
} else {
    echo __('It looks like there are no public .js files loaded or the ones visible do not follow <a href="https://codex.wordpress.org/Function_Reference/wp_enqueue_script">the WordPress way of enqueuing scripts</a>.', WPACU_PLUGIN_NAME);
}
/*
 * -------------------
 * [END] SCRIPTS LIST
 * -------------------
 */
