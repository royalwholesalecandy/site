<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

class Index extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Purchase Orders'));
        $this->_view->renderLayout();
    }
}
