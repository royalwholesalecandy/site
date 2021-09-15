<?php

namespace BoostMyShop\AdvancedStock\Model\StockDiscrepencies;

class UnconsistantReservedQuantity extends AbstractDiscrepencies
{
    protected $_warehouseItemCollectionFactory;
    protected $_router;

    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Router $router,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->_warehouseItemCollectionFactory = $warehouseItemCollectionFactory;
        $this->_router = $router;

        parent::__construct($stockRegistry);
    }

    public function run(&$results, $fix, $productId = null)
    {
        $results['unconsistant_reserved_quantity'] = ['explanations' => 'Unconsistant reserved quantity at warehouse item level', 'items' => []];

        //add discrepencies based on pending orders
        $collection = $this->_warehouseItemCollectionFactory->create();
        foreach($collection as $wi)
        {
            if (!$this->productManagesStock($wi->getwi_product_id()))
                continue;
            $expectedQty = $this->_router->getReservedQuantity($wi->getwi_product_id(), $wi->getwi_warehouse_id());
            if ($expectedQty != $wi->getwi_reserved_quantity()) {
                $results['unconsistant_reserved_quantity']['items'][] = 'product_id=' . $wi->getwi_product_id() . ', warehouse_id=' . $wi->getwi_warehouse_id() . ' (stored=' . $wi->getwi_reserved_quantity() . ',expected=' . $expectedQty . ')';

                if ($fix)
                {
                    $this->_router->updateQuantityToShip($wi->getwi_product_id(), $wi->getwi_warehouse_id());
                    $this->_router->updateReservedQuantity($wi->getwi_product_id(), $wi->getwi_warehouse_id());
                }
            }
        }

        if ($fix)
        {
            $collection = $this->_warehouseItemCollectionFactory->create()->addUnconsistentFilter();
            foreach($collection as $wi) {
                if (!$this->productManagesStock($wi->getwi_product_id()))
                    continue;
                $this->_router->updateQuantityToShip($wi->getwi_product_id(), $wi->getwi_warehouse_id());
                $this->_router->updateReservedQuantity($wi->getwi_product_id(), $wi->getwi_warehouse_id());
            }
        }

        //add discrepencies based on other information
        $collection = $this->_warehouseItemCollectionFactory->create()->addUnconsistentFilter();
        foreach($collection as $wi) {
            if (!$this->productManagesStock($wi->getwi_product_id()))
                continue;
            $results['unconsistant_reserved_quantity']['items'][] = 'product_id=' . $wi->getwi_product_id() . ', warehouse_id=' . $wi->getwi_warehouse_id() . ' - unconsistant value based on other information';
        }

        return $results;
    }

}