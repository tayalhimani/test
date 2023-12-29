<?php

namespace Dyson\AmastyCheckoutExtension\Model\Observer;

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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param JsonHelper $jsonHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        JsonHelper $jsonHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->jsonHelper = $jsonHelper;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
    }

    public function execute(EventObserver $observer)
    {
      try {
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
            $quoteId = $result[0]['entity_id'];

            //get the quote address fields
            $sql = "Select * from " . $this->resource->getTableName('quote_address') . " where quote_id = ". $quoteId . " and address_type = 'shipping'";
            $result = $connection->fetchAll($sql);
            $dialcode = $result[0]['dialcode'];

            //save the quote address fields in the sales_order_address table
            $sql = '';
            $sql .= "`" . 'dialcode' . "`='" . $dialcode . "', ";
            $sql = trim($sql, ", ");
            $sql = "Update " . $this->resource->getTableName('sales_order_address') . " Set " . $sql . " where parent_id = ". $orderId;
            $connection->query($sql);
        }
      } catch (\Exception $exception) {
          $this->logger->critical($exception->getMessage());
        }

    }
}
