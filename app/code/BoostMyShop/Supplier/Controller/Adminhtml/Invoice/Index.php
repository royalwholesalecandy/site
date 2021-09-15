<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Invoice;

class Index extends \BoostMyShop\Supplier\Controller\Adminhtml\Invoice
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Supplier Invoices'));
        $this->_view->renderLayout();
    }
}
