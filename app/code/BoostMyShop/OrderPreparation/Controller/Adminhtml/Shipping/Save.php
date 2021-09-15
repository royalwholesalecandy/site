<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Shipping;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Shipping
{

    /**
     * @return void
     */
    public function execute()
    {

        try
        {
            $trackings = $this->getRequest()->getPost('tracking');
            if (is_array($trackings))
            {
                foreach($trackings as $inProgressId => $trackingNumber)
                {
                    if ($trackingNumber)
                        $this->saveTracking($inProgressId, $trackingNumber);
                }
            }

            $this->messageManager->addSuccess(__('Trackings saved.'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__($ex->getMessage()));
        }


        $this->_redirect('*/*/index');
    }

    public function saveTracking($inProgressId, $trackingNumber)
    {
        $inProgress = $this->_inProgressFactory->create()->load($inProgressId);
        $inProgress->addTracking($trackingNumber);
        return $inProgress;
    }

    public function exportTemplate($templateId)
    {
        $template = $this->_templateFactory->create()->load($templateId);
        $orders = $this->getOrdersInProgress();

        $documentContent = $template->getShippingLabelFile($orders);
        $documentMimeType = $template->getct_export_file_mime();
        $documentFileName = $template->getct_export_file_name();

        return $this->_objectManager->get('\Magento\Framework\App\Response\Http\FileFactory')->create(
            $documentFileName,
            $documentContent,
            DirectoryList::VAR_DIR,
            $documentMimeType
        );
    }

    public function getOrdersInProgress()
    {
        $userId = $this->_preparationRegistry->getCurrentOperatorId();
        $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();
        $collection = $this->_inProgressCollectionFactory->create()->addUserFilter($userId)->addWarehouseFilter($warehouseId);
        $collection->addOrderDetails();

        return $collection;
    }

    public function processImport()
    {
        $templateId = $this->getRequest()->getPost('import_template');
        if (!$templateId)
            return;

        try
        {
            $destinationFolder = $this->_dir->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
            $uploader = $this->_uploaderFactory->create(array('fileId' => 'import_file'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setAllowedExtensions(['csv', 'txt']);
            $uploader->setFilesDispersion(true);
            $uploader->setAllowCreateFolders(true);
            $result = $uploader->save($destinationFolder);
            $fullPath = $result['path'].$result['file'];
            $fileContent = file_get_contents($fullPath);

            $template = $this->_templateFactory->create()->load($templateId);
            $result = $template->importTracking($fileContent);
            $this->messageManager->addSuccess(__('%1 trackings imported, %2 errors .', $result['success'], $result['error']));

        }
        catch(\Exception $ex)
        {

            if ($ex->getCode() != 666)
                $this->messageManager->addError($ex->getMessage());
        }

    }
}
