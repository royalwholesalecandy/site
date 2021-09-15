<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\LowStock;

class Index extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\LowStock
{

    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Stock Helper'));
        $this->_view->renderLayout();
    }

}
