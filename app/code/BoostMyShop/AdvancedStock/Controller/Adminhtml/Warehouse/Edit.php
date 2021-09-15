<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse;

class Edit extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse
{
    /**
     * @return void
     */
    public function execute()
    {

        $stockId = $this->getRequest()->getParam('w_id');

        $model = $this->_warehouseFactory->create();

        if ($stockId) {
            $model->load($stockId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This warehouse no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        }
        else
            $model->applyDefaultValues();

        $this->_coreRegistry->register('current_warehouse', $model);

        if (isset($stockId)) {
            $breadcrumb = __('Edit Warehouse');
        } else {
            $breadcrumb = __('New Warehouse');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Warehouses'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend($model->getId() ? __("Edit Warehouse '%1'", $model->getWName()) : __('New Warehouse'));
        $this->_view->renderLayout();
    }
}
