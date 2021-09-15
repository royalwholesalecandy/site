<?php

namespace BoostMyShop\UltimateReport\Controller\Adminhtml\OrderPreparation;

class Dashboard extends \BoostMyShop\UltimateReport\Controller\Adminhtml\OrderPreparation
{

    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Order Preparation Dashboard'));
        $this->_view->renderLayout();
    }

}
