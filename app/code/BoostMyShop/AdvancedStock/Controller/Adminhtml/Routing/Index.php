<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Routing;

class Index extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Routing
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Warehouses Routing'));
        $this->_view->renderLayout();
    }
}
