<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\ProductSupplier;

class Index extends \BoostMyShop\Supplier\Controller\Adminhtml\ProductSupplier
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Product / Supplier association'));
        $this->_view->renderLayout();
    }
}
