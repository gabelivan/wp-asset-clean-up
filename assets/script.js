/**
 * WP Asset Clean Up
 */
jQuery(document).ready(function($) {
    //
    // Common Code For Both Dashboard and Front-end View
    //
    var WpAssetCleanUp = {
        load: function() {
            var cbSelector = '.icheckbox_square-red', handle;

            $(cbSelector).iCheck({
                checkboxClass: 'icheckbox_square-red',
                increaseArea: '20%' // optional
            });

            $(cbSelector).on('ifChecked', function (event) {
                $(event.target).closest('tr').addClass('wpacu_not_load');
            });

            $(cbSelector).on('ifUnchecked', function (event) {
                $(event.target).closest('tr').removeClass('wpacu_not_load');
            });

            // Unload Everywhere
            $('.wpacu_global_unload').click(function() {
                handle = $(this).attr('data-handle');

                if ($(this).prop('checked')) {
                    $(this).parent('label').addClass('wpacu_global_checked');
                    //$(this).closest('tr').addClass('wpacu_not_load');

                    /*
                    if ($(this).hasClass('wpacu_global_style')) {
                        $('#style_' + handle).iCheck('check').iCheck('disable');
                    } else if($(this).hasClass('wpacu_global_script')) {
                        $('#script_' + handle).iCheck('check').iCheck('disable');
                    }
                    */
                } else {
                    $(this).parent('label').removeClass('wpacu_global_checked');
                    //$(this).closest('tr').removeClass('wpacu_not_load');

                    /*
                    if ($(this).hasClass('wpacu_global_style')) {
                        $('#style_' + handle).iCheck('uncheck').iCheck('enable');
                    } else if($(this).hasClass('wpacu_global_script')) {
                        $('#script_' + handle).iCheck('uncheck').iCheck('enable');
                    }
                    */

                    /*
                    // Un-check make exception as it is not relevant
                    // if unload everywhere is selected
                    $('#wpacu_style_load_it_' + handle)
                        .prop('checked', false)
                        .parent('label').removeClass('wpacu_global_unload_exception');
                    */
                }
            });

            /*
            // Asset Global Options
            $('.wpacu_global_option').click(function() {
                var handle = $(this).attr('data-handle'), handleType;

                if ($(this).hasClass('wpacu_style')) {
                    handleType = 'style';
                } else if ($(this).hasClass('wpacu_script')) {
                    handleType = 'script';
                }

                if ($(this).val() == 'remove') {
                    $(this).closest('tr').removeClass('wpacu_not_load');
                    $('#wpacu_load_it_option_'+ handleType +'_'+ handle).hide();
                }

                if ($(this).val() == 'default'
                    && !$('#wpacu_'+ handleType +'_load_it_'+ handle).prop('checked')) {
                    $(this).closest('tr').addClass('wpacu_not_load');
                }

                if ($(this).val() == 'default') {
                    $('#wpacu_load_it_option_'+ handleType + '_' + handle).show();
                }
            });
            */

            $('.wpacu_post_type_unload').click(function() {
                if ($(this).prop('checked')) {
                    $(this).parent('label').addClass('wpacu_post_type_unload_active');
                } else {
                    $(this).parent('label').removeClass('wpacu_post_type_unload_active');
                }
            });

            // Load it checkbox
            $('.wpacu_load_it_option').click(function() {
                /*
                var handle = $(this).attr('data-handle'), wpacu_input_name;

                if ($(this).hasClass('wpacu_style')) {
                    wpacu_input_name = 'wpacu_options_styles['+ handle +']';
                } else if ($(this).hasClass('wpacu_script')) {
                    wpacu_input_name = 'wpacu_options_scripts['+ handle +']';
                }
                */

                if ($(this).prop('checked')) {
                    $(this).parent('label').addClass('wpacu_global_unload_exception');
                    //$(this).closest('tr').removeClass('wpacu_not_load');
                ///} else if ($('input[name="'+ wpacu_input_name +'"]:checked').val() == 'default') {
                //    $(this).parent('label').removeClass('wpacu_global_unload_exception');
                    //$(this).closest('tr').addClass('wpacu_not_load');
                } else {
                    $(this).parent('label').removeClass('wpacu_global_unload_exception');
                }
            });
        }
    };

    $('#wpacu_post_type_select').change(function() {
        $('#wpacu_post_type_form').submit();
    });

    // Items are marked for removal from the unload list
    // from either "Everywhere" or "Post Type"
    $('.wpacu_remove_rule').click(function() {
        var $wpacuGlobalRuleRow = $(this).parents('.wpacu_global_rule_row');

        if ($(this).prop('checked')) {
            $wpacuGlobalRuleRow.addClass('selected');
        } else {
            $wpacuGlobalRuleRow.removeClass('selected');
        }
    });

    //
    // Asset Front-end Edit (if setting is enabled)
    //
    if ($('#wpacu_wrap_assets').length > 0) {
        WpAssetCleanUp.load();
    }

    //
    // The code below is for the pages loaded on the Dashboard
    //
    if (typeof wpacu_object === 'undefined' || $('#wpacu_meta_box_content').length < 1) {
        return false; // stop if we are not on the right page (with asset list)
    }

    // Get URL
    var data = wpacu_object.plugin_name + '_load=1';

    jQuery.post(wpacu_object.post_url, data, function (contents) {
        var data = {
            'action': wpacu_object.plugin_name + '_get_loaded_assets',
            'contents': contents,
            'post_id': wpacu_object.post_id,
            'post_url': wpacu_object.post_url
        };

        jQuery.post(wpacu_object.ajax_url, data, function (response) {
            if (response == '') {
                return false;
            }

            $('#wpacu_meta_box_content').html(response);

            if ($('#wpacu_home_page_form').length > 0) {
                $('#submit').show();
            }

            WpAssetCleanUp.load();
        });
    });
});

