<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\ProductSupplier;

class Import extends \BoostMyShop\Supplier\Controller\Adminhtml\ProductSupplier
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Import Product / Supplier association'));
        $this->_view->renderLayout();
    }
}
