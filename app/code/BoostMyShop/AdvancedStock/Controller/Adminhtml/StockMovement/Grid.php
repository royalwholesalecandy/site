<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockMovement;

class Grid extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockMovement
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();

    }
}
