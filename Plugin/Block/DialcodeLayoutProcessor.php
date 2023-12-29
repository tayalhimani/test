<?php

namespace Dyson\AmastyCheckoutExtension\Plugin\Block;

class DialcodeLayoutProcessor extends \Amasty\Checkout\Plugin\LayoutProcessor
{

    /**
     * @var \Amasty\Checkout\Model\Config
     */
    protected $checkoutConfig;

    protected $helpercheckout;

    public function __construct(
        \Amasty\Checkout\Helper\Onepage\Proxy $onepageHelper,
        \Amasty\Checkout\Model\Config $checkoutConfig,
        \Dyson\AmastyCheckoutExtension\Helper\JsonConfig $helpercheckout
    ) {
        $this->checkoutConfig = $checkoutConfig;
        $this->helpercheckout = $helpercheckout;
        parent::__construct($onepageHelper,$checkoutConfig);
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $result
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $result
    ) {
        if ($this->checkoutConfig->isEnabled()) {
            $layoutRoot = &$result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children'];

            foreach ($this->orderFixes as $code => $order) {
                $layoutRoot[$code]['sortOrder'] = $order;
            }
            if ($this->helpercheckout->isDialcodeEnabled()) {
                    $dialCodeValue = $this->helpercheckout->getDialcodeValueByWebsite();
                    $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['config']['elementTmpl'] = 'Dyson_AmastyCheckoutExtension/form/element/shipping-dialcode-telephone';

                    if ($this->helpercheckout->getCountryCode() == "SA") {
                        $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['validation'] = ['telephone-sa' => true,"required-entry" => true];
                    }

                    $customAttributeCode = 'dialcode';
                    $dialcode = [
                        'component' => 'Magento_Ui/js/form/element/abstract',
                        'config' => [
                            'customScope' => 'shippingAddress.custom_attributes',
                            'template' => 'ui/form/field',
                            'elementTmpl' => 'ui/form/element/input',
                            'default' => $dialCodeValue['dialcode']
                        ],
                        'provider' => 'checkoutProvider',
                        'dataScope' => 'shippingAddress.custom_attributes' . '.' . $customAttributeCode,
                        'validation' => [
                            'required-entry' => false
                        ],
                        'filterBy' => null,
                        'customEntry' => null,
                        'visible' => false
                    ];

                    $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$customAttributeCode] = $dialcode;

                    $configuration = $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];

                    foreach ($configuration as $paymentGroup => $groupConfig) {
                        if (isset($groupConfig['component']) AND $groupConfig['component'] === 'Magento_Checkout/js/view/billing-address') {
                            $billing_address_fields = $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children'];

                            $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']['telephone']['config']['elementTmpl'] = 'Dyson_AmastyCheckoutExtension/form/element/shipping-dialcode-telephone_billing';

                            if ($this->helpercheckout->getCountryCode() == "SA") {
                                $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']['telephone']['validation'] = ['telephone-sa' => true,"required-entry" => true];
                            }

                            $dialcode = [
                                'component' => 'Magento_Ui/js/form/element/abstract',
                                'config' => [
                                    'customScope' => $groupConfig['dataScopePrefix'],
                                    'template' => 'ui/form/field',
                                    'elementTmpl' => 'ui/form/element/input',
                                    'default' => $dialCodeValue['dialcode']
                                ],
                                'provider' => 'checkoutProvider',
                                'dataScope' => $groupConfig['dataScopePrefix'] . '.custom_attributes.dialcode',
                                'validation' => [
                                    'required-entry' => false
                                ],
                                'filterBy' => null,
                                'customEntry' => null,
                                'visible' => false
                            ];

                            $billing_address_fields['dialcode'] = $dialcode;
                        }
                        
                    }


            } else {
                $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['config']['elementTmpl'] = 'Amasty_Checkout/form/element/shipping-telephone';
            }

            if ($this->helpercheckout->getCountryCode() == "MY") {
                $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['validation'] = ['telephone-my' => true];

                $configuration = $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];

                foreach ($configuration as $paymentGroup => $groupConfig) {
                    if (isset($groupConfig['component']) AND $groupConfig['component'] === 'Magento_Checkout/js/view/billing-address') {
                        $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']['telephone']['validation'] = ['telephone-my' => true];
                    }
                }
            }

        }

        return $result;
    }
}
