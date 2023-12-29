<?php


namespace Dyson\SinglePageCheckout\Plugin\Magento\Quote\Model;

class GuestPaymentInformationManagement
{

    protected $logger;

    protected $request;

    protected $quoteIdMaskFactory;
    
    protected $quoteFactory;
    /**
     * __construct function
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Webapi\Rest\Request $request,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteFactory = $quoteFactory;
    }
    /**
     * @inheritdoc
     */
    public function aftersavePaymentInformation(
        \Magento\Checkout\Model\GuestPaymentInformationManagement $subject,
        $result,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($billingAddress) {

            $bodyparams = $this->request->getBodyParams();
            $dialcodevalue = '';
            if (isset($bodyparams['billingAddress']['customAttributes'])) {
                $dialcodekey = array_search('dialcode', array_column($bodyparams['billingAddress']['customAttributes'], 'attribute_code'));
                $dialcodevalue = isset($bodyparams['billingAddress']['customAttributes'][$dialcodekey]['value']) ?
                $bodyparams['billingAddress']['customAttributes'][$dialcodekey]['value'] : '';
            }
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            /** @var Quote $quote */

            if ($dialcodevalue) {
                
                try {
                    $quote = $this->quoteFactory->create()->load($quoteIdMask->getQuoteId());
                    $billingdata = $quote->getBillingAddress();
                    $billingdata->setDialcode($dialcodevalue);
                    $quote->setBillingAddress($billingdata);
                    $quote->save();

                } catch (\Exception $e) {
                    $this->logger->critical($e->getMessage());
                }
            }
        }
        return $result;
    }
}
