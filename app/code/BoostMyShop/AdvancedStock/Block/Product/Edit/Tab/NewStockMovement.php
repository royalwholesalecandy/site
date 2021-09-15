<?php
namespace BoostMyShop\AdvancedStock\Block\Product\Edit\Tab;

class NewStockMovement extends AbstractTab
{
    protected $_template = 'Product/Edit/Tab/NewStockMovement.phtml';

    public function getWarehouses()
    {
        die('ok');
        return $this->_warehouseCollectionFactory->create()->addActiveFilter();
    }

    public function getCategories()
    {
        return $this->_categories->toOptionArray();
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('advancedstock/product/createStockMovement');
    }

}