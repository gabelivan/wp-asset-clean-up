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
