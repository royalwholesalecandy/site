<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

use Magento\Framework\App\Filesystem\DirectoryList;

class MassExport extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    public function execute()
    {
        $this->_auth->getAuthStorage()->setIsFirstPageAfterLogin(false);

        $ids = $this->getRequest()->getPost('entity_ids');
        if (!is_array($ids))
            $ids = explode(',', $ids);

        try{

            $collection = $this->_orderCollectionFactory->create()->addFieldToFilter('po_id', ['in' => $ids]);
            $orders = [];
            foreach($collection as $order)
                $orders[] = $order;

            $supplier = $this->getSupplier($orders);

            $fileName = $this->_fileExport->getFileName($orders, $supplier);
            $csv = $this->_fileExport->getFileContent($orders, $supplier);

            return $this->_fileFactory->create(
                $fileName,
                $csv,
                DirectoryList::VAR_DIR,
                'application/csv'
            );

        } catch(\Exception $e){
            $this->messageManager->addError(__('An error occurred : '.$e->getMessage()));
        }

        $this->_redirect('*/*/index');
    }

    protected function getSupplier($orders)
    {
        $supplierId = false;
        foreach($orders as $order)
        {
            if (!$supplierId)
                $supplierId = $order->getpo_sup_id();
            else
            {
                if ($supplierId != $order->getpo_sup_id())
                    throw new \Exception('Mass export must be done for purchase orders with the same supplier only');
            }
        }

        return $this->_supplierFactory->create()->load($supplierId);
    }

}
