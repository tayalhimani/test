<?php
/**
 * @author Goram & Vincent
 * @package Dyson_SinglePageCheckout
 */

namespace Dyson\SinglePageCheckout\Plugin\View\Page\Config;

use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Config\Renderer as MagentoRenderer;

class Renderer
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    /** 
     * Remove Amasty checkout css.
     *
     * @param MagentoRenderer $subject
     * @param array $resultGroups
     *
     * @return array
     */
    public function beforeRenderAssets(MagentoRenderer $subject, $resultGroups = [])
    {
        $collection = $this->config->getAssetCollection();
        $collection->remove('Amasty_Checkout::css/source/mkcss/amcheckout.css');
        return [$resultGroups];
    }
}
