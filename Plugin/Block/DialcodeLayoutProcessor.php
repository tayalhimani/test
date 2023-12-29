<?php

namespace Dyson\SinglePageCheckout\Plugin\Block;

use Amasty\CheckoutCore\Block\Onepage\LayoutProcessor;
use Amasty\Checkout\Model\Config;
use Dyson\SinglePageCheckout\Helper\Data;
use Dyson\SinglePageCheckout\Helper\JsonConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class DialcodeLayoutProcessor
{
    /**
     * @var Config
     */
    protected $checkoutConfig;

    protected $helpercheckout;
    /**
     * @var Data
     */
    protected $singlePageHelper;
    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Config $checkoutConfig
     * @param JsonConfig $helpercheckout
     * @param Data $singlePageHelper
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Config $checkoutConfig,
        JsonConfig $helpercheckout,
        Data $singlePageHelper,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->checkoutConfig = $checkoutConfig;
        $this->helpercheckout = $helpercheckout;
        $this->singlePageHelper = $singlePageHelper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $result
     * @return array
     */
    public function afterProcess(
        LayoutProcessor $subject,
        $result
    ) {
        $telephoneValidation = $this->helpercheckout->getTelephoneValidation();

        $countryCode = $this->singlePageHelper->getCountryCode();
        if ($countryCode == "PL") {
            $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['vat_id']['sortOrder'] = 44;
        }
        if ($this->singlePageHelper->isCountyLabelEnabled())
        {
            $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['country_id']['visible'] = false;

            $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['country_label'] = [
                'component' => 'Dyson_SinglePageCheckout/js/form/element/country-label',
                'config' => [
                    'template' => 'ui/form/field',
                    'customScope' => 'shippingAddress.country_label',
                    'elementTmpl' =>  'Dyson_SinglePageCheckout/form/element/country-label'
                ],
                'dataScope' => 'shippingAddress.country_label',
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => [],
                'sortOrder' => $this->getCountryLabelSortOrder(),
                'id' => 'country_label',
                'additionalClasses' => 'custom-country-label'
            ];

            if($this->helpercheckout->getCountryCode() == 'SG'){
               $this->moveCountryLabel($result);
            }
        }
        $configuration = $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];


        foreach ($configuration as $paymentGroup => $groupConfig) {
            if (isset($groupConfig['component']) AND $groupConfig['component'] === 'Magento_Checkout/js/view/billing-address') {
                $billing_address_fields = &$result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children'];

                if ($this->singlePageHelper->isCountyLabelEnabled()) {
                    $billing_address_fields['country_id']['visible'] = false;
                    $billing_address_fields['country_label'] = [
                        'component' => 'Dyson_SinglePageCheckout/js/form/element/country-label',
                        'config' => [
                            'template' => 'ui/form/field',
                            'customScope' => $groupConfig['dataScopePrefix'],
                            'elementTmpl' =>  'Dyson_SinglePageCheckout/form/element/bill-country-label'
                        ],
                        'dataScope' => $groupConfig['dataScopePrefix'] . '.custom_attributes.country_label',
                        'provider' => 'checkoutProvider',
                        'visible' => true,
                        'validation' => [],
                        'sortOrder' => $this->getCountryLabelSortOrder(),
                        'id' => $groupConfig['dataScopePrefix'] . '_country_label',
                        'additionalClasses' => 'custom-country-label'
                    ];
                }
            }
        }

        if ($this->helpercheckout->isDialcodeEnabled() && $dialCodeValue = $this->helpercheckout->getDialcodeValueByWebsite()) {

            $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['config']['elementTmpl'] = 'Dyson_SinglePageCheckout/form/element/shipping-dialcode-telephone';

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
            if ($this->helpercheckout->getCountryCode() == "MX") {
                $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['validation'] = ['telephone-mx' => true,"required-entry" => true];
            }

            if (in_array($this->helpercheckout->getCountryCode(), ['CZ', 'SK'])) {
                $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['validation'] = [$telephoneValidation['shipping_validation'] => true,"required-entry" => true];
            }

            if ($this->helpercheckout->getCountryCode() == "TR") {
                $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['validation'] = [$telephoneValidation['shipping_validation'] => true,"required-entry" => true];

                $customAttributeCode = 'dialcode';
                $dialcode = [
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        'customScope' => 'shippingAddress.custom_attributes',
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input',
                        'default' => str_replace('5', '', $dialCodeValue['dialcode'])
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
            }
            $configuration = $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];

            foreach ($configuration as $paymentGroup => $groupConfig) {
                if (isset($groupConfig['component']) AND $groupConfig['component'] === 'Magento_Checkout/js/view/billing-address') {
                    $billing_address_fields = &$result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children'];

                    $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']['telephone']['config']['elementTmpl'] = 'Dyson_SinglePageCheckout/form/element/shipping-dialcode-telephone_billing';

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

                    if ($this->helpercheckout->getCountryCode() == "SG") {
                        $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']['telephone']['validation'] = [$telephoneValidation['billing_validation'] => true,"required-entry" => true];
                    }

                    if (in_array($this->helpercheckout->getCountryCode(), ['CZ', 'SK'])) {
                        $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']['telephone']['validation'] = [$telephoneValidation['billing_validation'] => true,"required-entry" => true];
                    }

                    if ($this->helpercheckout->getCountryCode() == "TR") {
                        $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']['telephone']['validation'] = [$telephoneValidation['billing_validation'] => true,"required-entry" => true];

                        $dialcode = [
                            'component' => 'Magento_Ui/js/form/element/abstract',
                            'config' => [
                                'customScope' => $groupConfig['dataScopePrefix'],
                                'template' => 'ui/form/field',
                                'elementTmpl' => 'ui/form/element/input',
                                'default' => str_replace('5', '', $dialCodeValue['dialcode'])
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

            }


        } else {
            $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['config']['elementTmpl'] = 'Dyson_SinglePageCheckout/form/element/shipping-telephone';
        }

        if ($this->helpercheckout->getCountryCode() == "SK") {
            $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['validation'] = ['telephone-my' => true];
            $configuration = $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];
            foreach ($configuration as $paymentGroup => $groupConfig) {
                if (isset($groupConfig['component']) AND $groupConfig['component'] === 'Magento_Checkout/js/view/billing-address') {
                    $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']['telephone']['validation'] = ['telephone-my' => true];
                }
            }
        }

        if ($this->helpercheckout->getCountryCode() == "VN") {
            $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['validation'] = ['telephone-vn' => true,'required-entry'=> true];
            $configuration = $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];

            foreach ($configuration as $paymentGroup => $groupConfig) {
                if (isset($groupConfig['component']) AND $groupConfig['component'] === 'Magento_Checkout/js/view/billing-address') {
                    $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']['telephone']['validation'] = ['telephone-vn' => true,'required-entry'=> true];
                }
            }
        }

        return $result;
    }

    /**
     * @return mixed
     */
    private function getCountryLabelSortOrder()
    {
        $getCountryLabelSortOrder = $this->scopeConfig->getValue('dyson_singlepagecheckout/country_label_sort_order/sortorder', ScopeInterface::SCOPE_STORE);
        return $getCountryLabelSortOrder;
    }

    public function moveCountryLabel(&$result){

        $shippingAddressFieldset = &$result['components']['checkout']['children']['steps']['children']
        ['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];
        if (array_key_exists('country_label', $shippingAddressFieldset)) {
            $shippingAddressFieldset['cppickup-tab-group']['children']['tab-1-content']['children']['country_label'] = $shippingAddressFieldset['country_label'];
            unset($shippingAddressFieldset['country_label']);
        }
    }
}
