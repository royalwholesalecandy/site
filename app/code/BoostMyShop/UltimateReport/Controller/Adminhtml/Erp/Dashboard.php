<?php

namespace BoostMyShop\UltimateReport\Controller\Adminhtml\Erp;

class Dashboard extends \BoostMyShop\UltimateReport\Controller\Adminhtml\Erp
{

    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('ERP Dashboard'));
        $this->_view->renderLayout();
    }

}
