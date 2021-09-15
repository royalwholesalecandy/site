<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing;

class Index extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Packing'));
        $this->_view->renderLayout();
    }
}
