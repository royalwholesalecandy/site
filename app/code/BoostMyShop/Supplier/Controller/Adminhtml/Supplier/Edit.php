<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Supplier;

class Edit extends \BoostMyShop\Supplier\Controller\Adminhtml\Supplier
{
    /**
     * @return void
     */
    public function execute()
    {

        $supplierId = $this->getRequest()->getParam('sup_id');

        $model = $this->_supplierFactory->create();

        if ($supplierId) {
            $model->load($supplierId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This supplier no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        }
        else
            $model->applyDefaultData();

        $this->_coreRegistry->register('current_supplier', $model);

        if (isset($supplierId)) {
            $breadcrumb = __('Edit Supplier');
        } else {
            $breadcrumb = __('New Supplier');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Suppliers'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend($model->getId() ? __("Edit Supplier '%1'", $model->getSupName()) : __('New Supplier'));
        $this->_view->renderLayout();
    }
}
