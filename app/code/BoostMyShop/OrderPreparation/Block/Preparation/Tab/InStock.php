<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class InStock extends \BoostMyShop\OrderPreparation\Block\Preparation\Tab
{

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('tab_stock');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

    public function getAllowedOrderStatuses()
    {
        return $this->_config->create()->getOrderStatusesForTab('instock');
    }

    public function addAdditionnalFilters($collection)
    {

    }


    public function getGridUrl()
    {
        return $this->getUrl('*/*/instockAjaxGrid', ['_current' => true, 'grid' => 'instock']);
    }


    protected function _prepareColumns()
    {
        $this->_eventManager->dispatch('bms_order_preparation_instock_grid', ['grid' => $this]);

        return parent::_prepareColumns();
    }

}
