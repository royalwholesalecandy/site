<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\MassStockEditor    ;

class Grid extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\MassStockEditor
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
