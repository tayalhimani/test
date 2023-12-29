<?php


namespace Dyson\SinglePageCheckout\Plugin\Magento\Quote\Model;

class BillingAddressManagement
{

    protected $logger;

    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function beforeAssign(
        \Magento\Quote\Model\BillingAddressManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $address,
        $useForShipping = false
    ) {
        $extAttributes = $address->getExtensionAttributes();
        if (!empty($extAttributes)) {
            try {
                $address->setDialcode($extAttributes->getDialcode());
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
        return [$cartId,$address,$useForShipping];
    }
}
