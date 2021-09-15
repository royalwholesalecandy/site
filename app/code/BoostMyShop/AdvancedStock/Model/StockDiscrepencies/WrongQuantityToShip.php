<?php

namespace BoostMyShop\AdvancedStock\Model\StockDiscrepencies;

class WrongQuantityToShip extends AbstractDiscrepencies
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
        $results['wrong_quantity_to_ship'] = ['explanations' => 'Quantity to ship in warehouse item doesnt match to the pending orders', 'items' => []];

        $collection = $this->_warehouseItemCollectionFactory->create();
        foreach($collection as $wi)
        {
            if (!$this->productManagesStock($wi->getwi_product_id()))
                continue;
            $expectedQty = $this->_router->getQuantityToShip($wi->getwi_product_id(), $wi->getwi_warehouse_id());
            if ($expectedQty != $wi->getwi_quantity_to_ship()) {
                if ($fix)
                {
                    //fix
                    $this->_router->updateQuantityToShip($wi->getwi_product_id(), $wi->getwi_warehouse_id());
                    $results['wrong_quantity_to_ship']['items'][] = "fix quantity to ship for product ".$wi->getwi_product_id()." and warehouse ".$wi->getwi_warehouse_id();
                    $expectedQty = $this->_router->getQuantityToShip($wi->getwi_product_id(), $wi->getwi_warehouse_id());
                }
            }
            if ($expectedQty != $wi->getwi_quantity_to_ship()) {

                $results['wrong_quantity_to_ship']['items'][] = 'product_id=' . $wi->getwi_product_id() . ', warehouse_id=' . $wi->getwi_warehouse_id() . ' (stored=' . $wi->getwi_quantity_to_ship() . ',expected=' . $expectedQty . ')';
            }
        }

        return $results;
    }

}