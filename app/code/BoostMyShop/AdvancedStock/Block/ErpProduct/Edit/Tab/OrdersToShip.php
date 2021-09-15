<?php

namespace BoostMyShop\AdvancedStock\Block\ErpProduct\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class OrdersToShip extends \BoostMyShop\AdvancedStock\Block\Product\Edit\Tab\PendingOrders\Grid implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('orderstoship');
    }


    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        return $this;
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    public function getGridUrl()
    {
        return $this->getUrl('advancedstock/erpproduct_orderstoship/grid', ['product_id' => $this->getProduct()->getId()]);
    }


    public function getTabLabel()
    {
        return __('Orders to ship');
    }

    public function getTabTitle()
    {
        return __('Orders to ship');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

}
