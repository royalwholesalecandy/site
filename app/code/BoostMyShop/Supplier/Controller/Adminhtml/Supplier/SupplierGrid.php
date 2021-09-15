<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Supplier;

class SupplierGrid extends \BoostMyShop\Supplier\Controller\Adminhtml\Supplier
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
