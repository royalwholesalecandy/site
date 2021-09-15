<?php

namespace BoostMyShop\UltimateReport\Controller\Adminhtml\Supplier;

class Dashboard extends \BoostMyShop\UltimateReport\Controller\Adminhtml\Supplier
{

    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Suppliers Dashboard'));
        $this->_view->renderLayout();
    }

}
