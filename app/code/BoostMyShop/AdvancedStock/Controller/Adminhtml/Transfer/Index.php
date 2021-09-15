<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer;

class Index extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Stock Transfers'));
        $this->_view->renderLayout();
    }
}
