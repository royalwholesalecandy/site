<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse;

class WarehouseGrid extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
