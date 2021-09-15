<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Product;

class Index extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Product
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Products'));
        $this->_view->renderLayout();
    }
}
