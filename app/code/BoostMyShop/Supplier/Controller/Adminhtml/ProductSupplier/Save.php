<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\ProductSupplier;

class Save extends \BoostMyShop\Supplier\Controller\Adminhtml\ProductSupplier
{
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $result = [];
        $result['success'] = true;
        $result['message'] = '';

        if (isset($data['products'])) {
            $products = $data['products'];

            try {
                foreach ($products as $supId => $supplierData) {
                    foreach ($supplierData as $productId => $productSupplierData) {
                        $this->updateData($supId, $productId, $productSupplierData);
                    }
                }

            } catch (\Exception $ex) {
                $result['success'] = false;
                $result['message'] = $ex->getMessage();
                $result['stack'] = $ex->getTraceAsString();
            }
        }

        die(json_encode($result));

    }

    public function updateData($supId, $productId, $productSupplierData)
    {
        $supplier = $this->_supplierFactory->create()->load($supId);
        if ($supplier->isAssociatedToProduct($productId))
        {
            $productSupplier = $this->_supplierProductFactory->create()->loadByProductSupplier($productId, $supId);
            foreach($productSupplierData as $k => $v)
                $productSupplier->setData($k, $v);
            $productSupplier->save();
            return $productSupplier;
        }
    }

}
