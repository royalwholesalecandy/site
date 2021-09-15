<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Product;

class Edit extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Product
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

        $breadcrumb = __('Products');

        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__("%1 (%2)", $model->getName(), $model->getSku()));
        $this->_view->renderLayout();
    }
}
