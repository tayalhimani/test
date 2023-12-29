<?php

namespace Dyson\SinglePageCheckout\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class SampleConfigProvider
 */
class CheckoutDetailsProvider implements ConfigProviderInterface
{
    protected $helper;
    public function __construct(
        \Dyson\SinglePageCheckout\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'cityData' => [
                'cityOpt' => $this->helper->getCityOptions()
            ],
        ];
    }
}
