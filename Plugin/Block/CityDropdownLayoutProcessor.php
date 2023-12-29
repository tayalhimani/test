<?php

namespace Dyson\SinglePageCheckout\Plugin\Block;

class CityDropdownLayoutProcessor
{
    /**
     * @var \Dyson\SinglePageCheckout\Helper\Data
     */
    private $dataHelper;

    /**
     * @param \Dyson\SinglePageCheckout\Helper\Data $dataHelper
     */
    public function __construct(
        \Dyson\SinglePageCheckout\Helper\Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param \Amasty\CheckoutCore\Block\Onepage\LayoutProcessor $subject
     * @param array $result
     * @return array
     */
    public function afterProcess(
        \Amasty\CheckoutCore\Block\Onepage\LayoutProcessor $subject,
        array $result
    ) {
        if ($this->dataHelper->getCityEnableField())
        {
            $shipping_address_fields = &$result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];
            $city = &$shipping_address_fields['city'];
            $city = $this->getConfig();

            // Updating billing address fields: removing unnecessary fields & adding validation
            $configuration = $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];
            foreach ($configuration as $paymentGroup => $groupConfig) {
                if (isset($groupConfig['component']) and $groupConfig['component'] === 'Magento_Checkout/js/view/billing-address') {
                    $billing_address_fields = &$result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children'];
                    $billing_address_fields['city'] = $this->getBillingConfig($groupConfig['dataScopePrefix']);
                }
            }
        }

        return $result;
    }

    /**
     * @return $field
     */
    private function getConfig()
    {
        $field = [
            'component' => 'Magento_Ui/js/form/element/select',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                'id' => 'city',
                'additionalClasses' => 'field-city',
            ],
            'label' => __('City'),
            'sortOrder' => 80,
            'dataScope' => 'shippingAddress.city',
            'provider' => 'checkoutProvider',
            'customEntry' => null,
            'visible' => true,
            'caption' => __($this->dataHelper->getCityFieldCaption()),
            'options' => $this->dataHelper->getCityOptions(),
            'value' => '',
            'validation' => [
                'required-entry' => true
            ],
            'id' => 'city'
        ];
        return $field;
    }

    /**
     * @return $field
     */
    private function getBillingConfig($customScope)
    {
        $field = [
            'component' => 'Magento_Ui/js/form/element/select',
            'config' => [
                'customScope' => $customScope,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                'id' => 'city'
            ],
            'label' => __('City'),
            'sortOrder' => 80,
            'dataScope' => $customScope . ".city",
            'provider' => 'checkoutProvider',
            'customEntry' => null,
            'visible' => true,
            'caption' => __($this->dataHelper->getCityFieldCaption()),
            'options' => $this->dataHelper->getCityOptions(),
            'value' => '',
            'validation' => [
                'required-entry' => true
            ],
            'id' => 'city'
        ];
        return $field;
    }
}
