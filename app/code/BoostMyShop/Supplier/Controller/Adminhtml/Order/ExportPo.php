<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportPo extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    public function execute()
    {
        $poId = (int)$this->getRequest()->getParam('po_id');
        $order = $this->_orderFactory->create()->load($poId);

        $this->_auth->getAuthStorage()->setIsFirstPageAfterLogin(false);

        try{

            $csv = $this->_fileExport->getFileContent($order);
            $fileName = $this->_fileExport->getFileName($order);

            return $this->_fileFactory->create(
                $fileName,
                $csv,
                DirectoryList::VAR_DIR,
                'application/csv'
            );

        } catch(\Exception $e){
            $this->messageManager->addError(__('An error occurred : '.$e->getMessage()));
            $this->_redirect('*/*/index');
        }

        $this->_redirect('*/*/Edit', ['po_id' => $poId]);
    }

}
