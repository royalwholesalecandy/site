<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Shipping;

class Index extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Shipping
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shipping'));
        $this->_view->renderLayout();
    }
}
