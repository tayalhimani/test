<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 27/06/2019
 * Time: 10:06
 */

namespace Dyson\AmastyCheckoutExtension\Plugin\Controller;

use Magento\Store\Model\ScopeInterface;

class Index extends \Amasty\Checkout\Controller\Index\Index
{
    /**
     * @var \Amasty\Checkout\Model\Config
     */
    private $amCheckoutConfig;

    public function afterExecute(\Amasty\Checkout\Controller\Index\Index $subject, $resultPage)
    {
        //check that the resultPage is a return parent:execute and if so just return
        if (is_a($resultPage, 'Magento\Framework\View\Result\Page\Interceptor')) {
            return $resultPage;
        }

        //check that the resultPage is a redirect to checkout/cart, meaning the basket has gone so we should return that redirect
        if (is_a($resultPage, '\Magento\Framework\Controller\Result\Redirect')) {
            return $resultPage;
        }

        $resultPage = $this->resultPageFactory->create();

        if ($font = $this->scopeConfig->getValue('amasty_checkout/design/font', ScopeInterface::SCOPE_STORE)) {
            $resultPage->getConfig()->addRemotePageAsset(
                'https://fonts.googleapis.com/css?family=' . urlencode($font),
                'css'
            );
        }

        $resultPage->getLayout()->getUpdate()->addHandle('amasty_checkout');

        if ($this->scopeConfig->getValue('amasty_checkout/design/header_footer', ScopeInterface::SCOPE_STORE)) {
            $resultPage->getLayout()->getUpdate()->addHandle('amasty_checkout_headerfooter');
        }

        /** @var \Magento\Checkout\Block\Onepage $checkoutBlock */
        $checkoutBlock = $resultPage->getLayout()->getBlock('checkout.root');

        $checkoutBlock
            ->setTemplate('Dyson_AmastyCheckoutExtension::onepage.phtml')
            ->setData('amcheckout_helper', $this->onepageHelper);

        $resultPage->getConfig()->getTitle()->set(__('Checkout'));

        return $resultPage;
    }
}
