<?php
namespace WpAssetCleanUp;

/**
 * Class GetHelp
 * @package WpAssetCleanUp
 */
class GetHelp
{
    /**
     * @var array
     */
    public $data = array();

    /**
     * @var
     */
    public $page;

    /**
     *
     */
    public function page()
    {
        Main::instance()->parseTemplate('get-help', $this->data, true);
    }
}
