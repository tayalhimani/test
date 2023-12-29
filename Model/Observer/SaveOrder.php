<?php

namespace Dyson\SinglePageCheckout\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Visitor Observer
 */
class SaveOrder implements ObserverInterface
{
   /**
     * @var \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection $resource
     */
    protected $resource;

    /**
     * @var \Magento\Framework\App\ResourceConnection $resource
     */
    protected $orderRepository;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    protected $quoteRepository;

    /**
     * @param JsonHelper $jsonHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        JsonHelper $jsonHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->resource = $resource;
        $this->jsonHelper = $jsonHelper;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
    }

    public function execute(EventObserver $observer)
    {
        $orderIds = $observer->getOrderIds();
        $connection = $this->resource->getConnection();

        foreach($orderIds as $orderId)
        {
            //get the order increment id
            $order = $this->orderRepository->get($orderId);
            $orderIncrementId = $order->getIncrementId();

            //get the quote id
            $sql = "Select entity_id from " . $this->resource->getTableName('quote') . " where reserved_order_id = ". $orderIncrementId;
            $result = $connection->fetchAll($sql);
            $quoteId = ($result) ? $result[0]['entity_id'] : null;

            if(!empty($quoteId))
            {
                //get the quote address fields
                $shippingsql = "Select * from " . $this->resource->getTableName('quote_address') . " where quote_id = ". $quoteId . " and address_type = 'shipping'";
                $billingsql = "Select * from " . $this->resource->getTableName('quote_address') . " where quote_id = ". $quoteId . " and address_type = 'billing'";

                $shippingresult = $connection->fetchAll($shippingsql);
                $billingresult = $connection->fetchAll($billingsql);

                $shippingdialcode = $shippingresult[0]['dialcode'];
                $billingdialcode = $billingresult[0]['dialcode'];

                //save the quote address fields in the sales_order_address table
                $shippingsql = '';
                $shippingsql .= "`" . 'dialcode' . "`='" . $shippingdialcode . "', ";
                $shippingsql = trim($shippingsql, ", ");

                $billingsql = '';
                $billingsql .= "`" . 'dialcode' . "`='" . $billingdialcode . "', ";
                $billingsql = trim($billingsql, ", ");

                $shippingsql = "Update " . $this->resource->getTableName('sales_order_address') . " Set " . $shippingsql . " where parent_id = ". $orderId . " and address_type = 'shipping'";
                $connection->query($shippingsql);

                $billingsql = "Update " . $this->resource->getTableName('sales_order_address') . " Set " . $billingsql . " where parent_id = ". $orderId . " and address_type = 'billing'";
                $connection->query($billingsql);
            }
        }
    }
}
