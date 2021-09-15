<?php

namespace BoostMyShop\UltimateReport\Block\Supplier;

class Dashboard extends \BoostMyShop\UltimateReport\Block\AbstractContainer
{

    protected function _configure()
    {
        $this->showFilter('store');
        $this->showFilter('interval');
        $this->showFilter('group_by_date');
    }

    public function getPageCode()
    {
        return 'supplier_dashboard';
    }

}