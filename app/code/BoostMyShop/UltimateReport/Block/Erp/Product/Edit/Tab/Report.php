<?php

namespace BoostMyShop\UltimateReport\Block\Erp\Product\Edit\Tab;

class Report extends \BoostMyShop\UltimateReport\Block\AbstractContainer implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected function _configure()
    {
        $this->showFilter('store');
        $this->showFilter('interval');
        $this->showFilter('group_by_date');

        $this->addHiddenField('product_id', $this->getProduct()->getId());
    }

    public function getPageCode()
    {
        return 'erp_product_view';
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
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