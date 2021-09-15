<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Supplier;

class ProcessImport extends \BoostMyShop\Supplier\Controller\Adminhtml\Supplier
{
    /**
     * @return void
     */
    public function execute()
    {
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

            $delimiter = $this->getRequest()->getParam('delimiter');

            $importHandler = $this->_objectManager->create('BoostMyShop\Supplier\Model\Supplier\ImportHandler');
            $count = $importHandler->importFromCsvFile($fullPath, $delimiter);
            $this->messageManager->addSuccess(__('Csv file has been imported : %1 rows processed', $count));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError($ex->getMessage());
        }

        $this->_redirect('*/*/index');

    }
}
