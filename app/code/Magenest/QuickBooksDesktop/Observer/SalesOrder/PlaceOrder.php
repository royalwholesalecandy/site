<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Observer\SalesOrder;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface as ObserverInterface;
use Magenest\QuickBooksDesktop\Model\QueueFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Operation;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;
use Magenest\QuickBooksDesktop\Helper\CreateQueue;

/**
 * Class Save order on frontend
 * @package Magenest\QuickBooksDesktop\Observer\SalesOrder
 */
class PlaceOrder implements ObserverInterface
{
    /**
     * @var QueueFactory
     */
    protected $_queueFactory;
    /**
     * Core Config Data
     *
     * @var $_scopeConfig \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Mapping
     */
    public $_map;

    /**
     * @var CreateQueue
     */
    protected $_queueHelper;

    /**
     * Save constructor.
     * @param ManagerInterface $messageManager
     * @param ScopeConfigInterface $scopeConfig
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $loggerInterface,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        QueueFactory $queueFactory,
        Mapping $map,
        CreateQueue $queueHelper
    ) {
        $this->logger = $loggerInterface;
        $this->_orderFactory = $orderFactory;
        $this->messageManager = $messageManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_queueFactory = $queueFactory;
        $this->_map = $map;
        $this->_queueHelper = $queueHelper;
    }

    /**
     * event place order
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $orderId = $observer->getEvent()->getOrderIds()[0];
        $order = $this->_orderFactory->create()->load($orderId);
        $cusId = $order->getCustomerId();

        $companyId = $this->_queueHelper->getCompanyId();

        $qbOrderId = $this->_map->getCollection()
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('type', Type::QUEUE_SALESORDER)
            ->addFieldToFilter('entity_id', $orderId)
            ->getFirstItem()->getData();

        if (empty($qbOrderId)) {
            if (!$cusId) {
                $qbCustomerId = $this->_map->getCollection()
                    ->addFieldToFilter('company_id', $companyId)
                    ->addFieldToFilter('type', Type::QUEUE_GUEST)
                    ->addFieldToFilter('entity_id', $orderId)
                    ->getFirstItem()->getData();
                if (empty($qbCustomerId)) {
                    $this->_queueHelper->createGuestQueue($orderId, 'Add', Operation::OPERATION_ADD);
                }
            }
            if ($order->getStatus() == 'pending') {
                $this->_queueHelper->createSalesOrderQueue($orderId);
            }
        }
    }
}
