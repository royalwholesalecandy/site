<?php

namespace BoostMyShop\AdvancedStock\Plugin\CatalogInventory\Block\Stockqty;

class DefaultStockqty extends \Magento\CatalogInventory\Block\Stockqty\DefaultStockqty
{
    public function getStockQtyLeft()
    {
        $this->setTemplate('Magento_CatalogInventory::stockqty/default.phtml');

        $stockItem = $this->stockRegistry->getStockItem($this->getProduct()->getId(), $this->getProduct()->getStore()->getWebsiteId());
        $minStockQty = $stockItem->getMinQty();
        return $this->getStockQty() - $minStockQty;
    }

}