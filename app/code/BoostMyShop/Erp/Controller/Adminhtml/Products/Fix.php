<?php

namespace BoostMyShop\Erp\Controller\Adminhtml\Products;

class Fix extends \BoostMyShop\Erp\Controller\Adminhtml\Products
{
    /**
     * @return void
     */
    public function execute()
    {

        $id = $this->getRequest()->getParam('id');

        $product = $this->_productFactory->create()->load($id);
        $this->_coreRegistry->register('current_product', $product);

        try
        {
            $helper = $this->_objectManager->get('BoostMyShop\AdvancedStock\Helper\Product');
            $helper->Fix($id);

            $this->messageManager->addSuccess(__('Fixes applied.'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__('An error occured : '.$ex->getMessage()));
        }

        $this->_redirect('erp/products/edit', ['id' => $id]);
    }
}
