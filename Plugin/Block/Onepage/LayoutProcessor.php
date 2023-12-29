<?php

namespace Dyson\SinglePageCheckout\Plugin\Block\Onepage;

use Amasty\CheckoutCore\Block\Onepage\LayoutWalker;
use Amasty\CheckoutCore\Block\Onepage\LayoutWalkerFactory;
use Amasty\CheckoutCore\Model\Config as AmastyCheckoutConfig;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Helper\Data as CheckoutHelper;

class LayoutProcessor
{
    /**
     * @var AmastyCheckoutConfig
     */
    private $checkoutConfig;

    /**
     * @var LayoutWalkerFactory
     */
    private $walkerFactory;

    /**
     * @var LayoutWalker
     */
    private $walker;

    /**
     * @var \Dyson\SinglePageCheckout\Helper\Data
     */
    private $singlePageCheckoutHelper;


    public function __construct(
        AmastyCheckoutConfig $checkoutConfig,
        LayoutWalkerFactory $walkerFactory,
        \Dyson\SinglePageCheckout\Helper\Data $singlePageCheckoutHelper
    ) {
        $this->checkoutConfig = $checkoutConfig;
        $this->walkerFactory = $walkerFactory;
        $this->singlePageCheckoutHelper = $singlePageCheckoutHelper;
    }

    /**
     * @param \Amasty\CheckoutCore\Block\Onepage\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     *
     * @see \Amasty\CheckoutCore\Block\Onepage\LayoutProcessor
     *  For available walker tokens.
     */
    public function afterProcess(
        \Amasty\CheckoutCore\Block\Onepage\LayoutProcessor $subject,
        array $jsLayout
    ) {

       $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['config']['elementTmpl'] = 'Dyson_SinglePageCheckout/form/element/shipping-telephone';
        /* if ($this->singlePageCheckoutHelper->isPincodeModuleEnabled()) {
            // we will manage sort order and required through CSV
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['sortOrder'] = 2;
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['validation']['required-entry'] = true;

            // we will manage street address from Xml file
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['children']['0']['label'] = __('Street Address 1 *');
        } */

        // Leave this place if Amasty Checkout is soft disabled.
        if (!$this->checkoutConfig->isEnabled()) {
            return $jsLayout;
        }


        // $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['city']['sortOrder'] = 60;
        //
        // $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment-list']['children']['free-form']['children']['city']['sortOrder'] = 60;

        /** Added from SinglePageCheckoutOverrides **/
        // $configuration = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];
        // foreach ($configuration as $paymentGroup => $groupConfig) {
        //     if (isset($groupConfig['component']) AND $groupConfig['component'] === 'Magento_Checkout/js/view/billing-address') {
        //         $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']['telephone']['sortOrder'] = 2000;
        //     }
        // }
        /** Added from SinglePageCheckoutOverrides **/

        // Setup the walker from the incoming jsLayout.
        $this->walker = $this->walkerFactory->create(['layoutArray' => $jsLayout]);

        // Force our custom layout for checkout.
        $this->walker->setValue('{CHECKOUT}.config.template', 'Dyson_SinglePageCheckout/onepage/layouts/classic/2columns');

        // Override for editable items declared in Amasty Checkout's
        // LayoutProcessor. If !isCheckoutItemsEditable use layout xml value.
        if ($this->checkoutConfig->isCheckoutItemsEditable()) {
            $this->walker->setValue('{CART_ITEMS}.>>.details.component', 'Dyson_SinglePageCheckout/js/view/checkout/summary/item/details');
            $this->walker->setValue('{CART_ITEMS}.config.template', 'Dyson_SinglePageCheckout/checkout/summary/cart-items');
        }
        // 'Place order' button in sidebar - remove it completely.
        $this->walker->unsetByPath('{SIDEBAR}.>>.place-button');

        // SET IN ONEPAGE.JS, must change via js :(
        //$this->walker->setValue('{SHIPPING_ADDRESS}.config.template', 'Dyson_SinglePageCheckout/onepage/shipping/address');



        return $this->walker->getResult();
    }
}
