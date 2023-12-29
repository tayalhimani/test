<?php

namespace Dyson\SinglePageCheckout\Model\Config;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Dyson\SinglePageCheckout\Helper\JsonConfig as AmastyHelper;
use Magento\Checkout\Model\Session;
use Dyson\SinglePageCheckout\Helper\Data;
use Magento\Directory\Model\CountryFactory;

class ConfigProvider implements ConfigProviderInterface
{
    const XML_PATH_BILLING_TITLE_ENABLED = "dyson_singlepagecheckout/checkout/billing_title_enabled";
    const XML_PATH_BILLING_TITLE = "dyson_singlepagecheckout/checkout/billing_title";
    const XML_PATH_SHIPPING_TITLE = "dyson_singlepagecheckout/shipping_checkout/shipping_title";
    const XML_PATH_XML_PATH_TELEPHONE_MESSAGE_ENABLED = "dyson_singlepagecheckout/telephone/telephone_message_enabled";
    const XML_PATH_TELEPHONE_MESSAGE = "dyson_singlepagecheckout/telephone/telephone_message";
    const XML_PATH_PINCODE_ENABLED = "pincode/general/enable";
    const XML_PATH_PAYMENT_TYPES_AVAILABLE_PUSH_DATALAYER = "dyson_singlepagecheckout/checkout/payment_types_available_push_datalayer";
    const XML_PATH_DIALCODE_ENABLED = "dyson_singlepagecheckout/prefix_dialcode/prefix_dialcode_enabled";
    const XML_PATH_PREFIX_POSTALCODE_ENABLED = "dyson_singlepagecheckout/prefix_postalcode/enable_postal_prefix";
    const XML_PATH_COUNTRY_CODE_PATH = 'general/country/default';
    const CURRENCY_FORMAT_EN = 'currency_settings/general/currency_format';
    const XML_PATH_AFTERPAY_PAGE = 'after_pay/after_pay_settings/popup_pages';

    /**
     * @var ScopeConfigInterface
     * */
    private $scopeConfig;
    /**
     * amastyHelper variable
     *
     * @var Dyson\SinglePageCheckout\Helper\JsonConfig
     */
    private $amastyHelper;
    /**
     * singlepageHelper variable
     *
     * @var Data
     */
    private $singlepageHelper;
    /**
     * @var Session
     */
    private $checkoutSession;
     /**
     * @var Country
     */
    protected $country;

    /**
     * ConfigProvider constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param AmastyHelper $amastyHelper
     * @param Session $checkoutSession
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        AmastyHelper $amastyHelper,
        Data $singlepageHelper,
        CountryFactory $countryFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->amastyHelper = $amastyHelper;
        $this->singlepageHelper = $singlepageHelper;
        $this->checkoutSession = $checkoutSession;
        $this->countryFactory = $countryFactory;
    }

    /**
     * @return array
     */
    public function getConfig()
    {

        $isPostalCodePrefixEnabled = (bool) $this->scopeConfig->getValue(self::XML_PATH_PREFIX_POSTALCODE_ENABLED, ScopeInterface::SCOPE_STORE);
        if ($isPostalCodePrefixEnabled) {
            $iso2Code = $this->getIso2Code();
        }
        $additionalVariables['billingTitleEnabled'] = (bool) $this->scopeConfig->getValue(self::XML_PATH_BILLING_TITLE_ENABLED, ScopeInterface::SCOPE_STORE);
        $additionalVariables['billingTitle'] = $this->scopeConfig->getValue(self::XML_PATH_BILLING_TITLE, ScopeInterface::SCOPE_STORE);
        $additionalVariables['shippingTitle'] = $this->scopeConfig->getValue(self::XML_PATH_SHIPPING_TITLE, ScopeInterface::SCOPE_STORE);
        $additionalVariables['telephoneMessageEnabled'] = (bool) $this->scopeConfig->getValue(self::XML_PATH_XML_PATH_TELEPHONE_MESSAGE_ENABLED, ScopeInterface::SCOPE_STORE);
        $additionalVariables['telephoneMessage'] = $this->scopeConfig->getValue(self::XML_PATH_TELEPHONE_MESSAGE, ScopeInterface::SCOPE_STORE);
        $additionalVariables['dialcode'] = $this->getDialcodebyWebsite();
        $additionalVariables['telephone_validate_length'] = $this->amastyHelper->getTelephoneValidateLength();
        $additionalVariables['country_label_name'] = $this->singlepageHelper->getCountryname();
        $additionalVariables['paymentTypesAvailablePushDataLayer'] = (bool) $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_TYPES_AVAILABLE_PUSH_DATALAYER, ScopeInterface::SCOPE_STORE);
        $additionalVariables['is_pincode_validator_enabled'] = (bool) $this->scopeConfig->getValue(self::XML_PATH_PINCODE_ENABLED, ScopeInterface::SCOPE_STORE);
        $additionalVariables['coupon_message_summary'] = $this->getCouponMessageOnCart();
        $additionalVariables['dialCodeEnabled'] = (bool) $this->scopeConfig->getValue(self::XML_PATH_DIALCODE_ENABLED, ScopeInterface::SCOPE_STORE);
        $additionalVariables['prefix_postal_code'] = $iso2Code ?? "";
        $additionalVariables['currencyFormatEnabled'] = $this->currencyFormatEnabled();
        $additionalVariables['country_code'] = $this->singlepageHelper->getCountryCode();
        $additionalVariables['afterpayCheckoutEnabled'] = $this->getAfterpayCheckoutData();

        return $additionalVariables;
    }

    /**
     * @return false|mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getDialcodebyWebsite(){
        $prefix_data = $this->amastyHelper->getDialcodeValueByWebsite();
        if ($prefix_data) {
            return $prefix_data;
        } else {
            return false;
        }
    }

    /**
     * @return false|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCouponMessageOnCart(){
        $couponCode = $this->checkoutSession->getQuote()->getCouponCode();
        $couponMessages = $this->amastyHelper->getCouponMessageOnCartIfEnabled();
        if (!empty($couponMessages)) {
            if ($couponCode) {
                return $couponMessages['after_applied'] ?? '';
            } else {
                return $couponMessages['before_applied'] ?? '';
            }
        } else {
            return false;
        }
    }

    public function getCountryData(){
        $countryCode = $this->scopeConfig->getValue(self::XML_PATH_COUNTRY_CODE_PATH, ScopeInterface::SCOPE_STORE);
        return $this->countryFactory->create()->load($countryCode);
    }

    public function getIso2Code(){
        $iso2Code = "";
        $countryData = $this->getCountryData();
        if (!empty($countryData)) {
            $iso2Code = $countryData->getData('iso2_code');
        }
        return $iso2Code;
    }


    public function currencyFormatEnabled()
    {
        return $this->scopeConfig->getValue(self::CURRENCY_FORMAT_EN, ScopeInterface::SCOPE_STORE);
    }

    public function getAfterpayCheckoutData(){
        $afterPayPagesValue = $this->scopeConfig->getValue(self::XML_PATH_AFTERPAY_PAGE, ScopeInterface::SCOPE_STORE);
        if($afterPayPagesValue){
            $afterPayPopupPages = explode(',', $afterPayPagesValue);
            if (in_array('checkout_index_index', $afterPayPopupPages)) {
                return true;
            }
        }
        return false;
    }
}
