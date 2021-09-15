<?php
namespace BoostMyShop\AdvancedStock\Block\Product\Edit\Tab;

class Websites extends AbstractTab
{
    protected $_template = 'Product/Edit/Tab/Websites.phtml';

    public function getWebsites()
    {
        return $this->_stockItemCollectionFactory->create()->addProductFilter($this->getProduct()->getId())->joinWebsite();
    }

    public function getSellableIndex($websiteId)
    {
        $productId = $this->getProduct()->getId();
        $sellable = $this->_stockRegistryProvider->getStockStatus($productId, $websiteId);
        $sellable = $sellable->getstock_status();
        return $sellable;
    }


}