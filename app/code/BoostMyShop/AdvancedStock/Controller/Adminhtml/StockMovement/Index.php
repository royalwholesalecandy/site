<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockMovement;

class Index extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockMovement
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Stock Movements'));
        $this->_view->renderLayout();
    }
}
