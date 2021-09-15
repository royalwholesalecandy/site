<?php

namespace BoostMyShop\AdvancedStock\Block\ErpProduct\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class StockMovement extends \BoostMyShop\AdvancedStock\Block\StockMovement\Grid implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('stockmovement');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_stockMovementCollectionFactory->create();
        $this->_addAdditionnalFilterForCollection($collection);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _addAdditionnalFilterForCollection(&$collection)
    {
        $collection->addProductFilter($this->getProduct());
        return $collection;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->removeColumn('sku');
        $this->removeColumn('name');


        return $this;
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }


    public function getWarehouseOptions()
    {
        $options = [];
        foreach($this->_warehouseCollectionFactory->create()->addActiveFilter() as $item)
        {
            $options[$item->getId()] = $item->getw_name();
        }
        return $options;
    }

    public function getGridUrl()
    {
        return $this->getUrl('advancedstock/erpproduct_stockmovement/grid', ['product_id' => $this->getProduct()->getId()]);
    }


    public function getTabLabel()
    {
        return __('Stock movements');
    }

    public function getTabTitle()
    {
        return __('Stock movements');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        $excludedProductTypes = ['configurable', 'bundle','grouped'];

        if (in_array($this->getProduct()->getTypeId(), $excludedProductTypes))
            return true;
        else
            return false;
    }

}
