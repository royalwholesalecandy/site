<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class Holded extends \BoostMyShop\OrderPreparation\Block\Preparation\Tab
{

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('tab_holded');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

    public function getAllowedOrderStatuses()
    {
        return $this->_config->create()->getOrderStatusesForTab('holded');
    }

    public function addAdditionnalFilters($collection)
    {

    }


    public function getGridUrl()
    {
        return $this->getUrl('*/*/holdedAjaxGrid', ['_current' => true, 'grid' => 'holded']);
    }

    protected function _prepareColumns()
    {
        $this->_eventManager->dispatch('bms_order_preparation_holded_grid', ['grid' => $this]);

        return parent::_prepareColumns();
    }

}
