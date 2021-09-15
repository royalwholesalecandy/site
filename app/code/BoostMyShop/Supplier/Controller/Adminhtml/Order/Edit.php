<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

class Edit extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    /**
     * @return void
     */
    public function execute()
    {

        $poId = $this->getRequest()->getParam('po_id');

        $model = $this->_orderFactory->create();
        if ($poId) {
            $model->load($poId);
            if(!$model->hasMoqIssue()){
                $this->messageManager->addNotice(__('This order has MOQ issue, please check product list for more details.'));
            }
            if (!$model->getId()) {
                $this->messageManager->addError(__('This order no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        }
        else {
            $supId = $this->getRequest()->getPost('sup_id');
            $model->applyDefaultData($supId);
            $this->messageManager->addSuccess(__('To create the new purchase order, fill the estimated time of arrival and save, then you will be able to add products.'));
        }

        $this->_coreRegistry->register('current_purchase_order', $model);

        if (!$model->reachesMinimumOfOrder())
            $this->messageManager->addError(__('This order total is below the supplier minimum of order (%1).', $model->getSupplier()->getsup_minimum_of_order()));
        if (!$model->reachesCarriageFree())
            $this->messageManager->addError(__('This order total is below the supplier carrier free (%1).', $model->getSupplier()->getsup_carriage_free_amount()));

        if (isset($poId)) {
            $breadcrumb = __('Edit Purchase Order');
        } else {
            $breadcrumb = __('New Purchase Order');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Purchase Orders'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend($model->getId() ? __("Edit Purchase Order %1 (%2)", $model->getPoReference(), $model->getpo_type()) : __('New Purchase Order'));
        $this->_view->renderLayout();
    }
}
