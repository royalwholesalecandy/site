<?php

namespace BoostMyShop\Erp\Controller\Adminhtml\Products;

class Index extends \BoostMyShop\Erp\Controller\Adminhtml\Products
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('ERP - Products'));
        $this->_view->renderLayout();
    }
}
