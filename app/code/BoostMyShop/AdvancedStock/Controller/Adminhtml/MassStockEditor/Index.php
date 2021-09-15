<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\MassStockEditor;

class Index extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\MassStockEditor
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Mass Stock Editor'));
        $this->_view->renderLayout();
    }
}
