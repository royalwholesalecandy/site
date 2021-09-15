<?php

namespace BoostMyShop\AdvancedStock\Model\StockDiscrepencies;

class MissingExtendedSalesFlatOrderItems extends AbstractDiscrepencies
{
    protected $_extendedSalesFlatOrderItemFactory;
    protected $_orderItemFactory;
    protected $_extendedSalesFlatOrderItemCollectionFactory;
    protected $_salesOrderItemCollectionFactory;
    protected $_router;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ExtendedSalesFlatOrderItemFactory $extendedSalesFlatOrderItemFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\ExtendedSalesFlatOrderItem\CollectionFactory $extendedSalesFlatOrderItemCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $salesOrderItemCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Router $router,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->_extendedSalesFlatOrderItemFactory = $extendedSalesFlatOrderItemFactory;
        $this->_orderItemFactory = $orderItemFactory;
        $this->_extendedSalesFlatOrderItemCollectionFactory = $extendedSalesFlatOrderItemCollectionFactory;
        $this->_salesOrderItemCollectionFactory = $salesOrderItemCollectionFactory;
        $this->_router = $router;

        parent::__construct($stockRegistry);
    }

    public function run(&$results, $fix, $productId = null)
    {
        $results['missing_extended_sales_flat_order_item'] = ['explanations' => 'Missing extended information for sales order item', 'items' => []];

        $orderItemIds = $this->_salesOrderItemCollectionFactory->create()->addFieldToFilter('product_id', ['gt' => 0])->getAllIds();
        $extendedItemIds = $this->_extendedSalesFlatOrderItemCollectionFactory->create()->getAllOrderItemId();
        $missingItemsIds = array_diff($orderItemIds, $extendedItemIds);

        foreach($missingItemsIds as $itemId)
        {
            $results['missing_extended_sales_flat_order_item']['items'][] = 'item_id=' . $itemId;

            if ($fix) {
                $orderItem = $this->_orderItemFactory->create()->load($itemId);
                $extendedOrderItem = $this->_extendedSalesFlatOrderItemFactory->create()->createFromOrderItem($orderItem->getOrder(), $orderItem);
                //todo : do not hardcode warehouse #1, cause it may not exist !!
                $warehouseId = ($extendedOrderItem->getesfoi_warehouse_id() ? $extendedOrderItem->getesfoi_warehouse_id() : 1);
                $this->_router->updateQuantityToShip($orderItem->getproduct_id(), $warehouseId);
            }
        }

        return $results;
    }

}