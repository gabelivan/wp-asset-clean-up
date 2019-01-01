<?php
namespace WpAssetCleanUp;

/**
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
}
