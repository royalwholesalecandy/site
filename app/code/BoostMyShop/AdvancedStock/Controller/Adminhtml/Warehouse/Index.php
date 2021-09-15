<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse;

class Index extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Warehouses'));
        $this->_view->renderLayout();
    }
}
