<?php

namespace BoostMyShop\Supplier\Block\ErpProduct\Edit\Tab\Supplier;

use Magento\Backend\Block\Widget\Grid\Column;

class Grid extends \BoostMyShop\Supplier\Block\ProductSupplier\Grid
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('supplier');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    protected function addAdditionnalFilter(&$collection)
    {
        $collection->addProductFilter($this->getProduct()->getId());
        $collection->addAssociatedFilter();
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->removeColumn('sku');
        $this->removeColumn('name');

        $this->_exportTypes = [];
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);

        return $this;
    }

    public function getMainButtonsHtml()
    {
        //
    }


    protected function _prepareMassaction()
    {
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('supplier/erpproduct_supplier/grid', ['product_id' => $this->getProduct()->getId()]);
    }

}