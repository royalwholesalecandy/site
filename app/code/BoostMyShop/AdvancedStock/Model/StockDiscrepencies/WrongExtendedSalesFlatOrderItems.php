<?php

namespace BoostMyShop\AdvancedStock\Model\StockDiscrepencies;

class WrongExtendedSalesFlatOrderItems extends AbstractDiscrepencies
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
        $results['wrong_extended_sales_flat_order_item'] = ['explanations' => 'Wrong extended information for sales order item', 'items' => []];

        $extendedItems = $this->_extendedSalesFlatOrderItemCollectionFactory->create()->joinOrderItem()->joinOpenedOrder();
        foreach($extendedItems as $item)
        {
            $storedQtyToShip = $item->getesfoi_qty_to_ship();
            $expectedQty = $item->getQuantityToShip();
            if ($storedQtyToShip != $expectedQty)
            {
                $results['wrong_extended_sales_flat_order_item']['items'][] = 'item_id=' . $item->getId().' (stored: '.$storedQtyToShip.', expected: '.$expectedQty.')';
                if ($fix) {
                    $item->updateQtyToShip()->save();
                }
            }
        }

        return $results;
    }

}