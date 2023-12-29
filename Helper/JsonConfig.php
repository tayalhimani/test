<?php
namespace Dyson\AmastyCheckoutExtension\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\Driver\File as Driver;

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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Framework\Module\Dir
     */
    protected $dir;
    /**
     * @var File
     */
    private $file;
     /**
      * @var Driver
      */
    protected $driver;

    /**
     * General constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Dir $dir
     * @param File $file
     * @param Driver $driver
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
    private function getJsonPath()
    {
        return $this->dir->getDir('Dyson_AmastyCheckoutExtension').'/config';
    }
     /**
      * Get Json file contents
      *
      * @return mixed
      */
    private function getJsonContents()
    {
        $fileName = '/'.'dialcode.json';
        $filePath = $this->getJsonPath().$fileName;
        if ($this->file->fileExists($filePath)) {
            $str = $this->driver->fileGetContents($filePath, 0, null);
            return $str;
        } else {
            return '{}';
        }
    }
    /**
     * getDialcodeValueByWebsite function
     *
     * @return mixed
     */
    public function getDialcodeValueByWebsite(){
       $dial_code_enable =  $this->getCountryByWebsite(self::DIALCODE_PATH);
       $prefix_data = false;

       if ($dial_code_enable) {
            $country_code =  $this->getCountryByWebsite(self::COUNTRY_CODE_PATH);
            $arr_data = json_decode($this->getJsonContents(),true);
            if (isset($arr_data[$country_code])) {
                $prefix_data = $arr_data[$country_code];
                return $prefix_data;
            }
            return $prefix_data;
       } 
       return $prefix_data;
    }
    /**
     * isDialcodeEnabled function
     *
     * @return boolean
     */
    public function isDialcodeEnabled(){
        $dial_code_enable =  $this->getCountryByWebsite(self::DIALCODE_PATH);

        return $dial_code_enable;
    }
    /**
     * getCountryCode function
     *
     * @return string
     */
    public function getCountryCode(){
        $country_code =  $this->getCountryByWebsite(self::COUNTRY_CODE_PATH);

        return $country_code;
    }

    /**
     * getTelephoneValidateLength function
     *
     * @return string
     */
    public function getTelephoneValidateLength(){
        $telephone_length =  $this->getCountryByWebsite(self::TELEPHONE_VALIDATE_LENGTH);

        return $telephone_length;
    }
}
