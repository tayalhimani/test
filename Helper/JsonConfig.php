<?php
/**
 * /Dyson/SinglePageCheckout\Helper
 * Loading json contents
 */
namespace Dyson\SinglePageCheckout\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File as Driver;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir;
use Magento\Store\Model\ScopeInterface;
use mysql_xdevapi\Exception;

/**
 * Loading json contents
 * Class JsonConfig
 */
class JsonConfig extends AbstractHelper
{
    /**
     * Get country path
     */
    const COUNTRY_CODE_PATH = 'general/country/default';

    const DIALCODE_PATH = 'dyson_singlepagecheckout/prefix_dialcode/prefix_dialcode_enabled';

    const TELEPHONE_VALIDATE_LENGTH = 'dyson_singlepagecheckout/prefix_dialcode/prefix_dialcode_validate_length';
    /**
     * XML_PATH_COUPON_MESSAGE_STATUS
     */
    const XML_PATH_COUPON_MESSAGE_STATUS = 'cart/coupon_carts_summary_message/enable';

    const XML_PATH_COUPON_MESSAGE_BEFORE_APPLIED = 'cart/coupon_carts_summary_message/coupon_carts_summary_before_apply';

    const XML_PATH_COUPON_MESSAGE_AFTER_APPLIED = 'cart/coupon_carts_summary_message/coupon_carts_summary_after_apply';

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var Dir
     */
    protected $dir;
    /**
     * @var File
     */
    protected $file;
    /**
     * @var Driver
     */
    protected $driver;

    /**
     * General constructor.
     *
     * @param ScopeConfigInterface $_scopeConfig
     * @param Dir                  $dir
     * @param File                 $file
     * @param Driver               $driver
     */
    public function __construct(
        ScopeConfigInterface $_scopeConfig,
        Dir $dir,
        File $file,
        Driver $driver
    ) {
        $this->_scopeConfig = $_scopeConfig;
        $this->dir = $dir;
        $this->file = $file;
        $this->driver = $driver;
    }

    /**
     * Get Country code by website scope
     *
     * @return mixed
     */
    public function getCountryByWebsite($path)
    {
        return $this->_scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_WEBSITES
        );
    }
    /**
     * Get Json file directory
     *
     * @return string
     */
    private function getJsonPath(): string
    {
        return $this->dir->getDir('Dyson_SinglePageCheckout').'/config';
    }

    /**
     * Get Json file contents
     *
     * @return string
     * @throws FileSystemException
     */
    private function getJsonContents(): string
    {
        $fileName = '/'.'dialcode.json';
        $filePath = $this->getJsonPath().$fileName;
        if ($this->file->fileExists($filePath)) {
            return $this->driver->fileGetContents($filePath, 0, null);
        } else {
            return '{}';
        }
    }

    /**
     * @param boolean $is_not_checkout
     * @return array|false
     */
    public function getDialcodeValueByWebsite($is_not_checkout = null)
    {
        $dial_code_enable = $this->getCountryByWebsite(self::DIALCODE_PATH);
        $prefix_data = false;
        try {
            if ($dial_code_enable) {
                $country_code = $this->getCountryByWebsite(self::COUNTRY_CODE_PATH);
                $arr_data = json_decode((string) $this->getJsonContents(), true);

                if (isset($arr_data[$country_code])) {
                    $dialCode = $arr_data[$country_code]["dialcode"];
                    $outPut = is_array($dialCode) ? $dialCode[$is_not_checkout ? 1 : 0] : $dialCode;

                    return ["dialcode" => $outPut];
                }

                return $prefix_data;
            }
        } catch (\Exception $e) {
            $prefix_data = false;
        }

        return $prefix_data;
    }
    /**
     * isDialcodeEnabled function
     *
     * @return boolean
     */
    public function isDialcodeEnabled()
    {
        return ($this->getCountryByWebsite(self::DIALCODE_PATH)) ? $this->getCountryByWebsite(self::DIALCODE_PATH) : false;
    }

    /**
     * getCountryCode function
     *
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->getCountryByWebsite(self::COUNTRY_CODE_PATH);
    }

    /**
     * getTelephoneValidateLength function
     *
     * @return string
     */
    public function getTelephoneValidateLength()
    {
        return $this->getCountryByWebsite(self::TELEPHONE_VALIDATE_LENGTH);
    }

    /**
     * @return mixed
     */
    private function getCouponMessageAfterApplied()
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue(self::XML_PATH_COUPON_MESSAGE_AFTER_APPLIED, $storeScope);
    }
    /**
     * @return mixed
     */
    private function getCouponMessageBeforeApplied()
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue(self::XML_PATH_COUPON_MESSAGE_BEFORE_APPLIED, $storeScope);
    }
    /**
     * getCouponMessageOnCartIfEnabled function
     *
     * @return array
     */
    public function getCouponMessageOnCartIfEnabled(): array
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        $status = $this->_scopeConfig->getValue(self::XML_PATH_COUPON_MESSAGE_STATUS, $storeScope);
        $couponMessage = [];
        if ($status) {
            $couponMessage['before_applied'] = $this->getCouponMessageBeforeApplied();
            $couponMessage['after_applied'] = $this->getCouponMessageAfterApplied();
            return $couponMessage;
        }
        return $couponMessage;
    }

    /**
     * getTelephoneValidation function
     *
     * Added new function as the getDialcodeValueByWebsite method
     * has dependency on dialcode enabler
     *
     * @return mixed
     * @throws FileSystemException
     */
    public function getTelephoneValidation()
    {
        $telephoneValidation = ['shipping_validation' => '', 'billing_validation' => '','std_code' => ''];
        $countryCode =  $this->getCountryByWebsite(self::COUNTRY_CODE_PATH);
        $content = json_decode((string) $this->getJsonContents(), true);
        if (isset($content[$countryCode])) {
            $telephoneValidation['shipping_validation'] = $content[$countryCode]['shipping_validation']?? '';
            $telephoneValidation['billing_validation'] = $content[$countryCode]['billing_validation']?? '';
            $telephoneValidation['std_code'] = $content[$countryCode]['std_code']?? '';
        }
        return $telephoneValidation;
    }
}
