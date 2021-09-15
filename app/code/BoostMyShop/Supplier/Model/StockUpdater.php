<?php

namespace BoostMyShop\Supplier\Model;

use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

class StockUpdater
{
    protected $_stockRegistryProvider;
    protected $_stockConfiguration;

    public function __construct(
        StockRegistryProviderInterface $stockRegistryProvider,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->_stockRegistryProvider = $stockRegistryProvider;
        $this->_stockConfiguration = $stockConfiguration;
    }

    public function incrementStock($productId, $qty, $reason, $po)
    {
        $stockItem = $this->_stockRegistryProvider->getStockItem($productId, $this->_stockConfiguration->getDefaultScopeId());
        $newQty = ((int)$stockItem->getQty()) + $qty;
        $this->updateStock($productId, $newQty);
    }

    protected function updateStock($productId, $qty)
    {
        $stockItem = $this->_stockRegistryProvider->getStockItem($productId, $this->_stockConfiguration->getDefaultScopeId());

        $stockItem->setQty($qty);
        if ($this->_stockConfiguration->getCanBackInStock($stockItem->getStoreId()) && ($stockItem->getQty() > $stockItem->getMinQty()))
        {
            $stockItem->setIsInStock(true);
            $stockItem->setStockStatusChangedAutomaticallyFlag(true);
        }

        $stockItem->save();
    }

}
