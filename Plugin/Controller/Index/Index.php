<?php

namespace Dyson\SinglePageCheckout\Plugin\Controller\Index;

use Amasty\CheckoutCore\Model\Config;
use Magento\Checkout\Block\Onepage;
use Magento\Framework\Controller\ResultInterface;
use Amasty\CheckoutCore\Controller\Index\Index as AmastyCheckoutIndexIndex;


/**
 * Class Index
 * @package Dyson\SinglePageCheckout\Plugin\Controller\Index
 */
class Index extends AmastyCheckoutIndexIndex
{
    /**
     * @var Config
     */
    private $amCheckoutConfig;

    public function __construct(Config $am_checkout_config)
    {
        $this->amCheckoutConfig = $am_checkout_config;
    }

    public function afterExecute($subject, $resultPage)
    {

        if (!$this->amCheckoutConfig->isEnabled()) {
            return $resultPage;
        }

        // Check, is resultPage just a redirect to checkout/cart? If yes then
        // the basket has gone, let's return that redirect.
        if(is_a($resultPage, '\Magento\Framework\Controller\Result\Redirect'))
        {
            return $resultPage;
        }

        // Now we know $resultPage isn't a redirect.
        /** @var ResultInterface $resultPage */

        /** @var Onepage $checkoutBlock */
        //$checkoutBlock = $resultPage->getLayout()->getBlock('checkout.root');

        // We'd just like to change the template please. Its declared
        // in Amasty_Checkout\Controller\Index\Index execute, not layout xml,
        // so we have to change it in this plugin.  :'(

        //Because of implementation changes from 2.4, it was moved to layout. 
        //$checkoutBlock->setTemplate('Dyson_SinglePageCheckout::onepage.phtml');

        return $resultPage;
    }

}
