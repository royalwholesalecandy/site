<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\ErpProduct\Supplier;

class MassEdit extends \BoostMyShop\Supplier\Controller\Adminhtml\ErpProduct
{
    public function execute()
    {
        $this->_initAction();
        $data = $this->getRequest()->getPostValue();

        $pairs = $data['massaction'];
        $mode = $data['mode'];
        foreach($pairs as $pair)
        {
            list($supId, $productId) = explode('_', $pair);
            $supplier = $this->_supplierFactory->create()->load($supId);
            if (!$supplier->isAssociatedToProduct($productId))
                $supplier->associateProduct($productId);
            $productSupplier = $this->_supplierProductFactory->create()->loadByProductSupplier($productId, $supId);
            $data = $this->getDataFromMode($mode);
            foreach($data as $k => $v)
                $productSupplier->setData($k, $v);
            $productSupplier->save();
        }

        $this->messageManager->addSuccess(__('Products associated to suppliers.'));
        $productId = $this->getRequest()->getParam('product_id');
        $this->_redirect('erp/products/edit', ['id' => $productId, 'active_tab' => 'supplier']);

    }

    protected function getDataFromMode($mode)
    {
        $data = [];
        switch($mode)
        {
            case 'stock_to_0':
                $data['sp_stock'] = 0;
                break;
            case 'stock_to_999':
                $data['sp_stock'] = 999;
                break;
            case 'remove_primary':
                $data['sp_primary'] = 0;
                break;
            case 'remove_sku':
                $data['sp_sku'] = '';
                break;
            case 'remove_price':
                $data['sp_price'] = '';
                break;
        }
        return $data;
    }
}
