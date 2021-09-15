<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Transit;

class Index extends \BoostMyShop\Supplier\Controller\Adminhtml\Transit
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Products in transit'));
        $this->_view->renderLayout();
    }
}
