<?php

namespace BoostMyShop\UltimateReport\Block\Supplier\Edit\Tabs;

class Report extends \BoostMyShop\UltimateReport\Block\AbstractContainer implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected function _configure()
    {
        $this->showFilter('store');
        $this->showFilter('interval');
        $this->showFilter('group_by_date');

        $this->addHiddenField('supplier_id', $this->getSupplier()->getId());
    }

    public function getSupplier()
    {
        return $this->_coreRegistry->registry('current_supplier');
    }

    public function getPageCode()
    {
        return 'supplier_view';
    }

    public function getTabLabel()
    {
        return __('Reports');
    }

    public function getTabTitle()
    {
        return __('Reports');
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