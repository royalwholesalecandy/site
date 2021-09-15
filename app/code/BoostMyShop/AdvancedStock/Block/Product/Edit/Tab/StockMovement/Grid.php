<?php

namespace BoostMyShop\AdvancedStock\Block\Product\Edit\Tab\StockMovement;

use Magento\Backend\Block\Widget\Grid\Column;

class Grid extends \BoostMyShop\AdvancedStock\Block\StockMovement\Grid
{

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->removeColumn('sku');
        $this->removeColumn('name');

        return $this;
    }

    protected function _addAdditionnalFilterForCollection(&$collection)
    {
        return $collection->addProductFilter($this->getProduct());
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('advancedstock/product/stockMovementGrid', ['id' => $this->getProduct()->getId()]);
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

}
