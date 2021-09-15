<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Supplier;

class Import extends \BoostMyShop\Supplier\Controller\Adminhtml\Supplier
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Import suppliers'));
        $this->_view->renderLayout();
    }
}
