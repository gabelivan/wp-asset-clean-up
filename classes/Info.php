<?php
namespace WpAssetCleanUp;

/**
 * Gets information pages such as "Getting Started", "Help" and "Info"
 * Retrieves specific information about a plugin or a theme
 *
 * Class Info
 * @package WpAssetCleanUp
 */
class Info
{
	/**
	 *
	 */
	public function gettingStarted()
	{
		$data = array('for' => 'how-it-works');

		if (array_key_exists('wpacu_for', $_GET)) {
			$data['for'] = sanitize_text_field($_GET['wpacu_for']);
		}

		Main::instance()->parseTemplate('admin-page-getting-started', $data, true);
	}

    /**
     *
     */
    public function help()
    {
        Main::instance()->parseTemplate('admin-page-get-help', array(), true);
    }

	/**
	 *
	 */
	public function pagesInfo()
    {
	    Main::instance()->parseTemplate('admin-page-pages-info', array(), true);
    }

	/**
	 *
	 */
	public function license()
	{
		Main::instance()->parseTemplate('admin-page-license', array(), true);
	}

	/**
	 * @param $locationChild
	 * @param $allPlugins
	 *
	 * @return string
	 */
	public static function getPluginInfo($locationChild, $allPlugins)
	{
		foreach (array_keys($allPlugins) as $pluginFile) {
			if (strpos($pluginFile, $locationChild.'/') === 0) {
				return '<div class="icon-plugin-default"><div class="icon-area"></div></div> &nbsp; <span class="wpacu-child-location-name">'.$allPlugins[$pluginFile]['Name'].'</span>' . ' <span class="wpacu-child-location-version">v'.$allPlugins[$pluginFile]['Version'].'</span>';
			}
		}

		return $locationChild;
	}

	/**
	 * @param $locationChild
	 * @param $allThemes
	 *
	 * @return string
	 */
	public static function getThemeInfo($locationChild, $allThemes)
	{
		foreach (array_keys($allThemes) as $themeDir) {
			if ($locationChild === $themeDir) {
				$themeInfo = wp_get_theme($themeDir);
				return $themeInfo->get('Name') . ' <span class="wpacu-child-location-version">v'.$themeInfo->get('Version').'</span>';
			}
		}

		return $locationChild;
	}
}
