<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

class NewAction extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    /**
     * @return void
     */
    public function execute()
    {
        $breadcrumb = __('New Purchase Order');
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Purchase Orders'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend( __('New Purchase Order'));
        $this->_view->renderLayout();
    }
}
