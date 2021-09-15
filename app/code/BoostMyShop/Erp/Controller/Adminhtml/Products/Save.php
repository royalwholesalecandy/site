<?php

namespace BoostMyShop\Erp\Controller\Adminhtml\Products;

class Save extends \BoostMyShop\Erp\Controller\Adminhtml\Products
{
    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getPost('id');
        $product = $this->_productFactory->create()->load($id);
        $this->_coreRegistry->register('current_product', $product);

        try
        {
            $this->_eventManager->dispatch('erp_product_edit_save', ['product' => $product,  'post_data' => $this->getRequest()->getPost(), 'message_manager' => $this->messageManager]);

            $this->messageManager->addSuccess(__('Product details saved.'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addSuccess(__('An error occured : '.$ex->getMessage()));
        }

        $this->_redirect('erp/products/edit', ['id' => $id]);

    }
}
