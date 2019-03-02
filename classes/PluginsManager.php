<?php
namespace WpAssetCleanUp;

/**
 * Class PluginsManager
 * @package WpAssetCleanUp
 */
class PluginsManager
{
    /**
     * @var array
     */
    public $data = array();

	/**
	 *
	 */
	public function page()
    {
    	// Get active plugins and their basic information
	    $this->data['active_plugins'] = get_option('active_plugins', array());
	    $this->data['plugins_icons'] = json_decode(get_transient('wpacu_active_plugins_icons'), ARRAY_A);

	   // echo '<pre>'; print_r($this->data['plugins_icons']);
	    Main::instance()->parseTemplate('admin-page-plugins-manager', $this->data, true);
    }
}
