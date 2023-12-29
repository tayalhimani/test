<?php
namespace Dyson\AmastyCheckoutExtension\Plugin\Magento\Quote\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

class CouponManagementPlugin
{
    /**
     * @param \Magento\Quote\Model\CouponManagement $subject
     * @param $proceed
     * @param $cartId
     * @param $couponCode
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function aroundSet(\Magento\Quote\Model\CouponManagement $subject, $proceed, $cartId, $couponCode)
    {
        try {
            $result = $proceed($cartId, $couponCode);
        } catch (NoSuchEntityException $e) {
            $errorMessage = __('This code doesn’t look quite right. Please check and try again.');
            throw new NoSuchEntityException($errorMessage);
        }
        return $result;
    }
}
