<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Discrepency;

class Report extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Discrepency
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Stock Discrepencies Report'));
        $this->_view->renderLayout();
    }
}
