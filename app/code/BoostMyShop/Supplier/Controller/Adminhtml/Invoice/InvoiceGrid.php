<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Invoice;

class InvoiceGrid extends \BoostMyShop\Supplier\Controller\Adminhtml\Invoice
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
