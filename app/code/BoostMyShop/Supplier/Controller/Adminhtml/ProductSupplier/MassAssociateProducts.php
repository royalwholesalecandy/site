<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\ProductSupplier;

class MassAssociateProducts extends \BoostMyShop\Supplier\Controller\Adminhtml\ProductSupplier
{
    public function execute()
    {
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
        $this->_redirect('supplier/productSupplier/index');

    }

}
