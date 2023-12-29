<?php

namespace Dyson\SinglePageCheckout\Controller\Checkout;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Totals extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Rule
     */
    protected $saleRule;

    /**
     * @var Coupon
     */
    protected $coupon;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    public function __construct(
        JsonFactory $resultJsonFactory,
        Context $context,
        Coupon $coupon,
        Rule $saleRule,
        ProductRepositoryInterface $productRepository,
        ProductFactory $_productFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->coupon = $coupon;
        $this->saleRule = $saleRule;
        $this->_productRepository = $productRepository;
        $this->_productFactory = $_productFactory;

        parent::__construct($context);
    }

    /**
     * @return Json
     */
    public function execute()
    {
        $coupon_code = $this->getRequest()->getParam('coupon_code');
        $reload_totals = $coupon_code ? false : true;

        return $this->resultJsonFactory->create()->setData($reload_totals);
    }
}
