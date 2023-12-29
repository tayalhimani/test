<?php
namespace Dyson\SinglePageCheckout\Plugin;

use Magento\Quote\Model\Quote as QuoteEntity;
use Magento\Framework\Exception\LocalizedException;
use Dyson\SinglePageCheckout\Helper\JsonConfig;

/**
 * ChangeShippingAddress class
 */
class QuoteManagementPlugin
{
    /*
     * TR STD CODE
     */
    const TR_STDCODE = 5;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    public $scopeConfig;

    /**
     * QuoteManagementPlugin constructor.
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        JsonConfig $jsonConfig
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->jsonConfig = $jsonConfig;
    }

    /**
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param QuoteEntity $quote
     * @param array $orderData
     * @return array
     * @throws LocalizedException
     */
    public function beforeSubmit(
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        QuoteEntity $quote,
        $orderData = []
    ) {
        try{
            $shippingAddress = $quote->getShippingAddress();
            $billingAddress = $quote->getBillingAddress();

            $prefixDialcodeValue = $this->scopeConfig->getValue(
                'dyson_singlepagecheckout/prefix_dialcode/prefix_dialcode_enabled',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            );

            if($prefixDialcodeValue){
                $stdCode= $this->jsonConfig->getTelephoneValidation()['std_code'];
                if($shippingAddress && $shippingAddress->getTelephone() != null){
                    $shippingTelephone = $stdCode.$shippingAddress->getTelephone();
                    $shippingAddress->setTelephone($shippingTelephone);
                    $quote->setShippingAddress($shippingAddress);
                }
                if($billingAddress && $billingAddress->getTelephone() != null){
                    $billingTelephone = $stdCode.$billingAddress->getTelephone();
                    $billingAddress->setTelephone($billingTelephone);
                    $quote->setBillingAddress($billingAddress);
                }
            }
        }catch(\Exception $e){
            $this->logger->critical($e->getMessage());
        }
        return [$quote, $orderData];
    }
}
