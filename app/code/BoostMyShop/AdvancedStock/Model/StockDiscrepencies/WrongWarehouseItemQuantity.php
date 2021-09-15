<?php

namespace BoostMyShop\AdvancedStock\Model\StockDiscrepencies;

class WrongWarehouseItemQuantity extends AbstractDiscrepencies
{
    protected $_stockItemCollectionFactory;
    protected $_warehouseItemCollectionFactory;
    protected $_logger;

    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory,
        \BoostMyShop\AdvancedStock\Helper\Logger $logger,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->_warehouseItemCollectionFactory = $warehouseItemCollectionFactory;
        $this->_logger = $logger;

        parent::__construct($stockRegistry);
    }

    public function run(&$results, $fix, $productId = null)
    {
        $results['wrong_warehouse_item_quantity'] = ['explanations' => 'Quantity stored in warehouse item doesnt match to the stock movements', 'items' => []];

        $collection = $this->_warehouseItemCollectionFactory->create();
        foreach($collection as $wi)
        {
            if (!$this->productManagesStock($wi->getwi_product_id()))
                continue;

            $expectedQty = $wi->calculatePhysicalQuantityFromStockMovements();
            if ((int)$expectedQty != $wi->getwi_physical_quantity()) {
                $label = 'product_id=' . $wi->getwi_product_id() . ', warehouse_id=' . $wi->getwi_warehouse_id() . ' (stored=' . $wi->getwi_physical_quantity() . ',expected=' . $expectedQty . ')';
                $results['wrong_warehouse_item_quantity']['items'][] = $label;

                if ($fix)
                {
                    $this->_logger->log('Fix wrong_warehouse_item_quantity : '.$label, \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventory);
                    $wi->updatePhysicalQuantity(true);
                }
            }

            $expectedAvailableQty = max($wi->getwi_physical_quantity() - $wi->getwi_quantity_to_ship(), 0);
            if ($expectedAvailableQty!= $wi->getwi_available_quantity()) {
                $label = 'Wrong available quantity for product_id=' . $wi->getwi_product_id() . ', warehouse_id=' . $wi->getwi_warehouse_id() . ' (stored=' . $wi->getwi_available_quantity() . ',expected=' . $expectedAvailableQty . ')';
                $results['wrong_warehouse_item_quantity']['items'][] = $label;

                if ($fix)
                {
                    $this->_logger->log('Fix wrong_warehouse_item_quantity : '.$label, \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventory);
                    $wi->updatePhysicalQuantity(true);
                }
            }


        }

        return $results;
    }

}