<?php

namespace Dyson\AmastyCheckoutExtension\Model\Config;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Dyson\AmastyCheckoutExtension\Helper\JsonConfig as AmastyHelper;
use Magento\Checkout\Model\Session;
use Dyson\AmastyCheckoutExtension\Helper\Data as AmastyCheckoutExtensionData;

class ConfigProvider implements ConfigProviderInterface
{
    const XML_PATH_BILLING_TITLE_ENABLED = "dyson_singlepagecheckout/checkout/billing_title_enabled";
    const XML_PATH_BILLING_TITLE = "dyson_singlepagecheckout/checkout/billing_title";
    const XML_PATH_SHIPPING_TITLE = "dyson_singlepagecheckout/shipping_checkout/shipping_title";
    const XML_PATH_XML_PATH_TELEPHONE_MESSAGE_ENABLED = "dyson_singlepagecheckout/telephone/telephone_message_enabled";
    const XML_PATH_TELEPHONE_MESSAGE = "dyson_singlepagecheckout/telephone/telephone_message";


    /**
     * @var ScopeConfigInterface
     * */
    private $scopeConfig;
    /**
     * amastyHelper variable
     *
     * @var Dyson\AmastyCheckoutExtension\Helper\JsonConfig
     */
    private $amastyHelper;

    private $amastyCheckoutExtensionData;
    /**
     * __construct function
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param AmastyHelper $amastyHelper
     * @param Session $checkoutSession
     * @param AmastyCheckoutExtensionData $amastyCheckoutExtensionData
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        AmastyHelper $amastyHelper,
        Session $checkoutSession,
        AmastyCheckoutExtensionData $amastyCheckoutExtensionData
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->amastyHelper = $amastyHelper;
        $this->checkoutSession = $checkoutSession;
        $this->amastyCheckoutExtensionData = $amastyCheckoutExtensionData;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $additionalVariables['billingTitleEnabled'] = (bool) $this->scopeConfig->getValue(self::XML_PATH_BILLING_TITLE_ENABLED, ScopeInterface::SCOPE_STORE);
        $additionalVariables['billingTitle'] = $this->scopeConfig->getValue(self::XML_PATH_BILLING_TITLE, ScopeInterface::SCOPE_STORE);
        $additionalVariables['shippingTitle'] = $this->scopeConfig->getValue(self::XML_PATH_SHIPPING_TITLE, ScopeInterface::SCOPE_STORE);
        $additionalVariables['telephoneMessageEnabled'] = (bool) $this->scopeConfig->getValue(self::XML_PATH_XML_PATH_TELEPHONE_MESSAGE_ENABLED, ScopeInterface::SCOPE_STORE);
        $additionalVariables['telephoneMessage'] = $this->scopeConfig->getValue(self::XML_PATH_TELEPHONE_MESSAGE, ScopeInterface::SCOPE_STORE);
        $additionalVariables['dialcode'] = $this->getDialcodebyWebsite();
        $additionalVariables['telephone_validate_length'] = $this->amastyHelper->getTelephoneValidateLength();
        $additionalVariables['coupon_message_summary'] = $this->getCouponMessageOnCart();

        return $additionalVariables;
    }
    /**
     * getDialcodebyWebsite function
     *
     * @return mixed
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
     * getCouponMessageOnCart function
     *
     * @return mixed
     */
    private function getCouponMessageOnCart(){
        $conponeCode = $this->checkoutSession->getQuote()->getCouponCode();
        $couponMeesage = $this->amastyCheckoutExtensionData->getCouponMessageOnCartIfEnabled();

        if (!empty($couponMeesage)) {
            if ($conponeCode) {
                return isset($couponMeesage['after_applied']) ?
                $couponMeesage['after_applied'] : '';
            } else {
                return isset($couponMeesage['before_applied']) ?
                $couponMeesage['before_applied'] : '';
            }
        } else {
            return false;
        }

    }
}
