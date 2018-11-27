<?php
if (! isset($data)) {
	exit;
}

foreach ($data['all']['styles'] as $obj) {
	$data['row'] = array();
	$data['row']['obj'] = $obj;

	$active = (isset($data['current']['styles']) && in_array($data['row']['obj']->handle, $data['current']['styles']));

	$data['row']['class'] = $active ? 'wpacu_not_load' : '';
	$data['row']['checked'] = $active ? 'checked="checked"' : '';

	/*
	 * $data['row']['is_global_rule'] is only used to apply a red background in the style's area to point out that the style is unloaded
	 *               is set to `true` if either the asset is unloaded everywhere or it's unloaded on a group of pages (such as all pages belonging to 'page' post type)
	*/
	$data['row']['global_unloaded'] = $data['row']['is_post_type_unloaded'] = $data['row']['is_load_exception'] = $data['row']['is_global_rule'] = false;

	// Mark it as unloaded - Everywhere
	if (in_array($data['row']['obj']->handle, $data['global_unload']['styles'])) {
		$data['row']['global_unloaded'] = $data['row']['is_global_rule'] = true;
	}

	// Mark it as unloaded - for the Current Post Type
	if ($data['bulk_unloaded_type'] && in_array($data['row']['obj']->handle, $data['bulk_unloaded'][$data['bulk_unloaded_type']]['styles'])) {
		$data['row']['is_global_rule'] = true;

		if ($data['bulk_unloaded_type'] === 'post_type') {
			$data['row']['is_post_type_unloaded'] = true;
		}
	}

	if ($data['row']['is_global_rule']) {
		if (in_array($data['row']['obj']->handle, $data['load_exceptions']['styles'])) {
			$data['row']['is_load_exception'] = true;
		} else {
			$data['row']['class'] .= ' wpacu_not_load';
		}
	}

	$data['row']['extra_data_css_list'] = (is_object($data['row']['obj']->extra) && isset($data['row']['obj']->extra->after)) ? $data['row']['obj']->extra->after : array();

	if (! $data['row']['extra_data_css_list']) {
		$data['row']['extra_data_css_list'] = (is_array($data['row']['obj']->extra) && isset($data['row']['obj']->extra['after'])) ? $data['row']['obj']->extra['after'] : array();
	}

	$data['row']['class'] .= ' style_'.$data['row']['obj']->handle;

	// Load Template
	echo \WpAssetCleanUp\Main::instance()->parseTemplate(
        '/meta-box-loaded-assets/_asset-style-single-row',
        $data
    );
}
