<?php

namespace BoostMyShop\Erp\Controller\Adminhtml\Products;

class Edit extends \BoostMyShop\Erp\Controller\Adminhtml\Products
{
    /**
     * @return void
     */
    public function execute()
    {

        $id = $this->getRequest()->getParam('id');

        $model = $this->_productFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This product no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        }

        $this->_coreRegistry->register('current_product', $model);

        $breadcrumb = __('ERP - Products');

        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__("ERP - %1 (%2)", $model->getName(), $model->getSku()));
        $this->_view->renderLayout();
    }
}
