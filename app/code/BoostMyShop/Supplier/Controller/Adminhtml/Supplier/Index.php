<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Supplier;

class Index extends \BoostMyShop\Supplier\Controller\Adminhtml\Supplier
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Suppliers'));
        $this->_view->renderLayout();
    }
}
