<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\CarrierTemplate;

class Index extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\CarrierTemplate
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shipping Label Templates'));
        $this->_view->renderLayout();
    }
}
