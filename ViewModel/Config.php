<?php


namespace Dyson\SinglePageCheckout\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class Config
 * @package Dyson\SinglePageCheckout\ViewModel
 */
class Config implements ArgumentInterface
{
    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /**
     * Account constructor.
     * @param ScopeConfigInterface $scope_config
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scope_config)
    {
        $this->scopeConfig = $scope_config;
    }

    /**
     * Returns the system config for the given path.
     *
     * @param string $config_path
     * @return mixed
     */
    public function getConfig($config_path) {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}