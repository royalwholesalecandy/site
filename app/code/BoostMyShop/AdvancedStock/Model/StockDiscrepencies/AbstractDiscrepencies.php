<?php

namespace BoostMyShop\AdvancedStock\Model\StockDiscrepencies;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManagerFactory;

abstract class AbstractDiscrepencies
{
    protected $_stockRegistry;

    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->_stockRegistry = $stockRegistry;
    }

    public abstract function run(&$results, $fix, $productId = null);

    public function productManagesStock($productId)
    {
        //todo : exclude configurable / grouped / bundle products
        $stockitem = $this->_stockRegistry->getStockItem($productId);
        return $stockitem->getManageStock();
    }

}