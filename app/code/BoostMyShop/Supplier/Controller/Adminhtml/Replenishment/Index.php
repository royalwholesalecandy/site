<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Replenishment;

class Index extends \BoostMyShop\Supplier\Controller\Adminhtml\Replenishment
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Supply Needs'));
        $this->_view->renderLayout();
    }
}
