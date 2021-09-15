<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

class OrderGrid extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
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
