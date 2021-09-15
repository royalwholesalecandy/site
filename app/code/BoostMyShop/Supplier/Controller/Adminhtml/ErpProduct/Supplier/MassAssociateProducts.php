<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\ErpProduct\Supplier;

class MassAssociateProducts extends \BoostMyShop\Supplier\Controller\Adminhtml\ErpProduct
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();

        $data = $this->getRequest()->getPostValue();

        $pairs = $data['massaction'];
        foreach($pairs as $pair)
        {
            list($supId, $productId) = explode('_', $pair);
            $supplier = $this->_supplierFactory->create()->load($supId);
            if (!$supplier->isAssociatedToProduct($productId))
                $supplier->associateProduct($productId);
        }

        $this->messageManager->addSuccess(__('Products associated to suppliers.'));
        $productId = $this->getRequest()->getParam('product_id');
        $this->_redirect('erp/products/edit', ['id' => $productId, 'active_tab' => 'supplier']);

    }
}
