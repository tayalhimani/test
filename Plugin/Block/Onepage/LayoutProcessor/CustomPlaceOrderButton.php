<?php

namespace Dyson\SinglePageCheckout\Plugin\Block\Onepage\LayoutProcessor;

use Amasty\Checkout\Model\Config as ConfigProvider;

class CustomPlaceOrderButton
{
    /**
     * @var ConfigProvider
     */
    private $config;

    /**
     * @param ConfigProvider $config
     */
    public function __construct(
        ConfigProvider $config
    ) {
        $this->config = $config;
    }

    /**
     * Overridden Amasty Custom Place Order Button as it is Setting Place Order as Default Button Text
     * @param \Amasty\Checkout\Block\Onepage\LayoutProcessor\CustomPlaceOrderButton $subject
     * @param $result
     * @param $jsLayout
     * @return mixed
     */
    public function afterProcess(
        \Amasty\Checkout\Block\Onepage\LayoutProcessor\CustomPlaceOrderButton $subject,
        $result,
        $jsLayout
    ) {
        if (!$this->config->isCustomPlaceButtonText()) {
            return $jsLayout;
        }
        return $result;
    }
}
