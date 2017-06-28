<?php
namespace WpAssetCleanUp;

/**
 * Class AdvancedRules
 * @package WpAssetCleanUp
 */
class AdvancedRules
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
        Main::instance()->parseTemplate('settings-advanced-rules', $this->data, true);
    }
}
