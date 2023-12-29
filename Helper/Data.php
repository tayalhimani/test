<?php
namespace Dyson\SinglePageCheckout\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Dyson\SinglePageCheckout\Api\DysonCityRepositoryInterface;

class Data extends AbstractHelper
{
    const COUNTRY_LABEL_ENABLED = 'dyson_singlepagecheckout/country_label/country_label_enabled';
    const XML_PATH_DEFAULT_COUNTRY = 'general/country/default';
    const RELOAD_TOTALS_ENABLED = 'dyson_singlepagecheckout/reload_totals_at_checkout/reload_totals_enabled';
    const XML_PATH_IS_PINCODE_VALIDATOR_ENABLED = 'pincode/general/enable';
    const XML_PATH_CITY_FIELD = 'dyson_singlepagecheckout/enable_city_dropdown/enable_city_dropdown_enable';
    const XML_PATH_CITY_FIELD_CAPTION = 'dyson_singlepagecheckout/enable_city_dropdown/city_dropdown_caption';
    const XML_PATH_COUPON_MESSAGE_STATUS = 'cart/coupon_carts_summary_message/enable';
    const XML_PATH_COUPON_MESSAGE_BEFORE_APPLIED = 'cart/coupon_carts_summary_message/coupon_carts_summary_before_apply';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DysonCityRepositoryInterface
     */
    private $dysonCityRepo;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param CountryFactory $countryFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DysonCityRepositoryInterface $dysonCityRepo
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CountryFactory $countryFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DysonCityRepositoryInterface $dysonCityRepo
        )
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->countryFactory = $countryFactory;
        $this->dysonCityRepo = $dysonCityRepo;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get Current Store City Dropdown Options
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
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
     * Get Available City List
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
     * Get City Enable Field
     * @return boolean
     */
    public function getCityEnableField()
    {
        return $this->getConfigValue(self::XML_PATH_CITY_FIELD, $this->getStoreId());
    }

    /**
     * Get City Field Caption
     * @return string
     */
    public function getCityFieldCaption()
    {
        return $this->getConfigValue(self::XML_PATH_CITY_FIELD_CAPTION, $this->getStoreId());
    }

    /**
     * Get store config value
     *
     * @return string
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    /**
     * getCountryname function
     *
     * @return string
     */
    public function getCountryname(){
        $countryCode = $this->getConfigValue(self::XML_PATH_DEFAULT_COUNTRY, $this->getStoreId());
        try {
            $country = $this->countryFactory->create()->loadByCode($countryCode);
        } catch (\Exception $e) {
            $country = '';
        }

        return $country->getName();
    }

    /**
     * @return string
     */
    public function getCountryCode(){
        $countryCode = $this->getConfigValue(self::XML_PATH_DEFAULT_COUNTRY, $this->getStoreId());

        return $countryCode;
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    private function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Check If Country Label enabled
     *
     * @return string
     */
    public function isCountyLabelEnabled()
    {
        return $this->getConfigValue(self::COUNTRY_LABEL_ENABLED, $this->getStoreId());
    }

    /**
     * Check Reload Totals is enable
     *
     * @return int
     */
    public function isReloadTotalsEnable()
    {
        return $this->getConfigValue(self::RELOAD_TOTALS_ENABLED, $this->getStoreId());
    }

    /**
     * Check Pincode Validator module is enable
     *
     * @return int
     */
    public function isPincodeModuleEnabled()
    {
        return $this->getConfigValue(self::XML_PATH_IS_PINCODE_VALIDATOR_ENABLED, $this->getStoreId());
    }

    private function getCouponMessageAfterApplied(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_COUPON_MESSAGE_AFTER_APPLIED,$storeScope);
    }

    private function getCouponMessageBeforeApplied(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_COUPON_MESSAGE_BEFORE_APPLIED,$storeScope);

      }

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
