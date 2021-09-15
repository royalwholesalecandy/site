<?php

namespace BoostMyShop\UltimateReport\Block\Erp;

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
        return 'erp_dashboard';
    }

}