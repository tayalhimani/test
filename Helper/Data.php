<?php
namespace Dyson\AmastyCheckoutExtension\Helper;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Dyson\AmastyCheckoutExtension\Api\DysonCityRepositoryInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $storeManager;

    private $searchCriteriaBuilder;

    private $dysonCityRepo;

    protected $scopeConfig;

    /**
     * Recipient email config path
     */
    const XML_PATH_CITY_FIELD = 'dyson_singlepagecheckout/enable_city_dropdown/enable_city_dropdown_enable';

    const XML_PATH_COUPON_MESSAGE_STATUS = 'cart/coupon_carts_summary_message/enable';

    const XML_PATH_COUPON_MESSAGE_BEFORE_APPLIED = 'cart/coupon_carts_summary_message/coupon_carts_summary_before_apply';

    const XML_PATH_COUPON_MESSAGE_AFTER_APPLIED = 'cart/coupon_carts_summary_message/coupon_carts_summary_after_apply';

    /**
     * __construct function
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DysonCityRepositoryInterface $dysonCityRepo
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DysonCityRepositoryInterface $dysonCityRepo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->dysonCityRepo = $dysonCityRepo;
        $this->storeManager = $storeManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function getCityOptions()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $countryCode = $this->getCountryCode();
        $this->searchCriteriaBuilder->addFilter('store_id', $storeId, 'eq');
        $this->searchCriteriaBuilder->addFilter('country_code', $countryCode, 'eq');

        $cityData = $this->getAvailableCityList($this->searchCriteriaBuilder->create());
        $options = [];
        if ($cityData) {
            foreach ($cityData as $_cityData) {
                $options[] = ['value' => $_cityData->getCity(), 'label' => $_cityData->getCity()];
            }
        }

        return $options;
    }
    /**
     * getAvailableCityList function
     *
     * @param [type] $fieldName
     * @param [type] $fieldValue
     * @param [type] $filterType
     * @return void
     */
    private function getAvailableCityList($searchCriteria)
    {

        $cites = $this->dysonCityRepo->getList($searchCriteria);

        return $cites->getItems();
    }
    /**
     * getCityEnableField function
     *
     * @return boolean
     */
    public function getCityEnableField()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_CITY_FIELD, $storeScope);
    }

    /**
     * Return country code
     * @return mixed
     */
    public function getCountryCode()
    {
        return $this->scopeConfig->getValue('general/country/default');
    }

    /**
     * @return mixed
     */
    private function getCouponMessageAfterApplied(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_COUPON_MESSAGE_AFTER_APPLIED,$storeScope);

    }

    /**
     * @return mixed
     */
    private function getCouponMessageBeforeApplied(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_COUPON_MESSAGE_BEFORE_APPLIED,$storeScope);

    }
    /**
     * getCouponMessageOnCartIfEnabled function
     *
     * @return array
     */
    public function getCouponMessageOnCartIfEnabled(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $status = $this->scopeConfig->getValue(self::XML_PATH_COUPON_MESSAGE_STATUS,$storeScope);
        $couponMessage = [];

        if ($status) {
            $couponMessage['before_applied'] = $this->getCouponMessageBeforeApplied();
            $couponMessage['after_applied'] = $this->getCouponMessageAfterApplied();

            return $couponMessage;
        }

        return $couponMessage;
    }
}
