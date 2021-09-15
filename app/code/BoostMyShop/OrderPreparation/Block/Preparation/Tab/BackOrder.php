<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class BackOrder extends \BoostMyShop\OrderPreparation\Block\Preparation\Tab
{

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('tab_backorder');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

    public function getAllowedOrderStatuses()
    {
        $statuses = $this->_config->create()->getOrderStatusesForTab('outofstock');
        return $statuses;
    }

    public function addAdditionnalFilters($collection)
    {

    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/backorderAjaxGrid', ['_current' => true, 'grid' => 'backorder']);
    }

    protected function _prepareColumns()
    {
        $this->_eventManager->dispatch('bms_order_preparation_backorder_grid', ['grid' => $this]);

        return parent::_prepareColumns();
    }

}
