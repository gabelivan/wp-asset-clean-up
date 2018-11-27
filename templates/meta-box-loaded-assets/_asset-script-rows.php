<?php
if (! isset($data)) {
	exit;
}

foreach ($data['all']['scripts'] as $obj) {
	$data['row'] = array();
	$data['row']['obj'] = $obj;

	$active = (isset($data['current']['scripts']) && in_array($data['row']['obj']->handle, $data['current']['scripts']));

	$data['row']['class'] = $active ? 'wpacu_not_load' : '';
	$data['row']['checked'] = $active ? 'checked="checked"' : '';

	/*
	 * $data['row']['is_global_rule'] is only used to apply a red background in the script's area to point out that the script is unloaded
	*/
	$data['row']['global_unloaded'] = $data['row']['is_post_type_unloaded'] = $data['row']['is_load_exception'] = $data['row']['is_global_rule'] = false;

	// Mark it as unloaded - Everywhere
	if (in_array($data['row']['obj']->handle, $data['global_unload']['scripts']) && !$data['row']['class']) {
		$data['row']['global_unloaded'] = $data['row']['is_global_rule'] = true;
	}

	// Mark it as unloaded - for the Current Post Type
	if ($data['bulk_unloaded_type'] && in_array($data['row']['obj']->handle, $data['bulk_unloaded'][$data['bulk_unloaded_type']]['scripts'])) {
		$data['row']['is_global_rule'] = true;

		if ($data['bulk_unloaded_type'] === 'post_type') {
			$data['row']['is_post_type_unloaded'] = true;
		}
	}

	if ($data['row']['is_global_rule']) {
		if (in_array($data['row']['obj']->handle, $data['load_exceptions']['scripts'])) {
			$data['row']['is_load_exception'] = true;
		} else {
			$data['row']['class'] .= ' wpacu_not_load';
		}
	}

	$data['row']['extra_data_js'] = (is_object($data['row']['obj']->extra) && isset($data['row']['obj']->extra->data)) ? $data['row']['obj']->extra->data : false;

	if (! $data['row']['extra_data_js']) {
		$data['row']['extra_data_js'] = (is_array($data['row']['obj']->extra) && isset($data['row']['obj']->extra['data'])) ? $data['row']['obj']->extra['data'] : false;
	}

	$data['row']['class'] .= ' script_'.$data['row']['obj']->handle;

	// Load Template
	echo \WpAssetCleanUp\Main::instance()->parseTemplate(
		'/meta-box-loaded-assets/_asset-script-single-row',
		$data
	);
}
