<?php

namespace BoostMyShop\UltimateReport\Model\Report\Source\OrderPreparation;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager;

class PendingOrders extends \BoostMyShop\UltimateReport\Model\Report\Source\AbstractSource
{
    protected $_ordersFactory = null;
    protected $_config = null;
    protected $_inProgressCollectionFactory = null;
    protected $_extendedOrderItemCollectionFactory;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $ordersFactory,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory $inProgressCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\ExtendedSalesFlatOrderItem\CollectionFactory $extendedOrderItemCollectionFactory,
        \BoostMyShop\OrderPreparation\Model\Config $config
    )
    {
        $this->_ordersFactory = $ordersFactory;
        $this->_config = $config;
        $this->_inProgressCollectionFactory = $inProgressCollectionFactory;
        $this->_extendedOrderItemCollectionFactory = $extendedOrderItemCollectionFactory;
    }


    public function getReportDatas($max = null)
    {
        $data = [];

        $data[] = ['group' => 'In stock', 'qty' => $this->getOrderCount('instock')];
        $data[] = ['group' => 'Backorder', 'qty' => $this->getOrderCount('backorder')];
        $data[] = ['group' => 'Holded', 'qty' => $this->getOrderCount('holded')];

        return $data;
    }

    public function getOrderCount($type)
    {
        $collection = $this->_ordersFactory->create();
        $collection->addFieldToFilter('status', ['in' => $this->getAllowedOrderStatuses($type)]);

        $selectedOrderIds = $this->_inProgressCollectionFactory->create()->getOrderIds();
        if (count($selectedOrderIds) > 0)
            $collection->addFieldToFilter('entity_id', array('nin' => $selectedOrderIds));

        $this->addAdditionnalFilter($collection, $type);

        return $collection->getSize();
    }

    protected function getAllowedOrderStatuses($type)
    {
        switch($type)
        {
            case 'instock':
                return $this->_config->getOrderStatusesForTab('instock');
                break;
            case 'backorder':
                return $this->_config->getOrderStatusesForTab('outofstock');
                break;
            case 'holded':
                return $this->_config->getOrderStatusesForTab('holded');
                break;
        }
    }

    protected function addAdditionnalFilter($collection, $type)
    {
        switch($type)
        {
            case 'instock':
                $backOrderIds = $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->addProductTypeFilter()->addQtyToShipFilter()->addNotFullyReservedFilter()->getOrderIds();
                if (count($backOrderIds) > 0)
                    $collection->addFieldToFilter('entity_id', array('nin' => $backOrderIds));

                $toShipOrderIds = $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->addProductTypeFilter()->addQtyToShipFilter()->getOrderIds();
                if (count($toShipOrderIds) > 0)
                    $collection->addFieldToFilter('entity_id', array('in' => $toShipOrderIds));
                break;
            case 'backorder':
                $backOrderIds = $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->addProductTypeFilter()->addQtyToShipFilter()->addNotFullyReservedFilter()->getOrderIds();
                if (count($backOrderIds) > 0)
                    $collection->addFieldToFilter('entity_id', array('in' => $backOrderIds));
                else
                    $collection->addFieldToFilter('entity_id', array('in' => [-1]));
                break;
            case 'holded':
                $collection->addFieldToFilter('entity_id', ['in' => $this->getOpenedOrderIdForWarehouse()]);
                break;
        }
    }

    protected function getOpenedOrderIdForWarehouse()
    {
        return $this->_extendedOrderItemCollectionFactory->create()->addQtyToShipFilter()->joinOrderItem()->getOrderIds();
    }

}