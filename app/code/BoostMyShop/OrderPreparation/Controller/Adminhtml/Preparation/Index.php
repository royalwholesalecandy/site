<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

class Index extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Order Preparation'));
        $this->_view->renderLayout();
    }
}
