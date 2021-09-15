<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Shipping;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class TemplateImport extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Shipping
{

    /**
     * @return void
     */
    public function execute()
    {

        try
        {
            $templateId = $this->getRequest()->getPost('import_template');
            if (!$templateId)
                throw new \Exception('No template selected');

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
                $this->messageManager->addError($ex->getMessage());
        }

        $this->_redirect('*/*/index');
    }

}
