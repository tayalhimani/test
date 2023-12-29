<?php

namespace Dyson\AmastyCheckoutExtension\Plugin;

class LayoutProcessor
{
    private $dataHelper;
    /**
     * __construct function
     *
     * @param \Dyson\AmastyCheckoutExtension\Helper\Data $dataHelper
     */
    public function __construct(
        \Dyson\AmastyCheckoutExtension\Helper\Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }
    /**
     * @param LayoutProcessor $subject
     * @param array $result
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $result
    ) {
        if ($this->dataHelper->getCityEnableField()) {
            $shipping_address_fields = &$result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];
            unset($shipping_address_fields['region_id']);
            $city = &$shipping_address_fields['city'];

            $country_id = &$shipping_address_fields['country_id'];
            if (in_array($this->dataHelper->getCountryCode(), ["TH","SA"])) {
                $country_id["visible"] = false;
            }

            $city = $this->getConfig();

            // Updating billing address fields: removing unnecessary fields & adding validation
            $configuration = $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];
            foreach ($configuration as $paymentGroup => $groupConfig) {
                if (isset($groupConfig['component']) and $groupConfig['component'] === 'Magento_Checkout/js/view/billing-address') {
                    $billing_address_fields = &$result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children'];
                    unset($billing_address_fields['region_id']);
                    $billing_address_fields['city'] = $this->getBillingConfig($groupConfig['dataScopePrefix']);
                    if (in_array($this->dataHelper->getCountryCode(), ["TH","SA"])) {
                        $billing_address_fields['country_id']["visible"] = false;
                    }
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
        $caption = "";
        if ($this->dataHelper->getCountryCode() == "TH") {
            $caption = __('Please select your province');
        } else {
            $caption = __('Please select your city');
        }

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
            'caption' => $caption,
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
        $caption = "";
        if ($this->dataHelper->getCountryCode() == "TH") {
            $caption = __('Please select your province');
        } else {
            $caption = __('Please select your city');
        }

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
            'caption' => $caption,
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
