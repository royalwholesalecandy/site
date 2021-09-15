<?php
namespace BoostMyShop\AdvancedStock\Block\Product\Edit\Tab;

class Stocks extends AbstractTab
{
    protected $_template = 'Product/Edit/Tab/Stocks.phtml';

    public function getStocks()
    {
        return $this->_warehouseItemCollectionFactory->create()->addProductFilter($this->getProduct()->getId())->joinWarehouse();
    }

    public function getDefaultWarningStockLevel()
    {
        return $this->_config->getSetting('stock_level/default_warning');
    }

    public function getDefaulIdealStockLevel()
    {
        return $this->_config->getSetting('stock_level/default_ideal');
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('advancedstock/product/saveWarehouseItem');
    }

}