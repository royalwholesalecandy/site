<?php

namespace BoostMyShop\Supplier\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class ErpProductEditMainFigures implements ObserverInterface
{
    protected $_eventManager;
    protected $_orderProductCOllectionFactory;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Product\CollectionFactory $orderProductCOllectionFactory
    ) {
        $this->_eventManager = $eventManager;
        $this->_orderProductCOllectionFactory = $orderProductCOllectionFactory;
    }

    public function execute(EventObserver $observer)
    {
        $block = $observer->getEvent()->getblock();
        $product = $observer->getEvent()->getProduct();

        $qtyToReceive = $this->_orderProductCOllectionFactory->create()
                                    ->addProductFilter($product->getId())
                                    ->addExpectedFilter()
                                    ->addOrderStatusFilter(\BoostMyShop\Supplier\Model\Order\Status::expected)
                                    ->countToReceive();
        $block->addFigure('Qty to receive', $qtyToReceive);

        return $this;
    }
}
