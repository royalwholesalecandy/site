<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Payments;

class Index extends \BoostMyShop\Supplier\Controller\Adminhtml\Payments
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Supplier Payments'));
        $this->_view->renderLayout();
    }
}
