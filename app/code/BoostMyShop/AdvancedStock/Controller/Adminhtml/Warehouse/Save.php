<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse;

class Save extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse
{
    public function execute()
    {

        $stockId = (int)$this->getRequest()->getParam('w_id');
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            $this->_redirect('adminhtml/*/');
            return;
        }

        $model = $this->_warehouseFactory->create()->load($stockId);
        if ($stockId && $model->isObjectNew()) {
            $this->messageManager->addError(__('This warehouse no longer exists.'));
            $this->_redirect('adminhtml/*/');
            return;
        }

        $model->setData($data);


        try {
            $model->save();
            $this->messageManager->addSuccess(__('You saved the warehouse.'));

            $this->checkImport($stockId);


            $this->_redirect('*/*/Edit', ['w_id' => $model->getId()]);
        } catch (\Magento\Framework\Validator\Exception $e) {
            $messages = $e->getMessages();
            $this->messageManager->addMessages($messages);
            $this->redirectToEdit($model, $data);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
            $this->redirectToEdit($model, $data);
        }
    }

    /**
     * @param
     * @param array $data
     * @return void
     */
    protected function redirectToEdit(\BoostMyShop\Supplier\Model\Supplier $model, array $data)
    {
        $this->_getSession()->setUserData($data);
        $arguments = $model->getId() ? ['w_id' => $model->getId()] : [];
        $arguments = array_merge($arguments, ['_current' => true, 'active_tab' => '']);
        $this->_redirect('adminhtml/*/edit', $arguments);
    }

    protected function checkImport($stockId)
    {
        try
        {
            $adapter = $this->_httpFactory->create();
            if ($adapter->isValid('import_file')) {
                $destinationFolder = $this->_dir->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
                $uploader = $this->_uploaderFactory->create(array('fileId' => 'import_file'));
                $uploader->setAllowRenameFiles(true);
                $uploader->setAllowedExtensions(['csv', 'txt']);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destinationFolder);
                $fullPath = $result['path'].$result['file'];

                $importHandler = $this->_objectManager->create('BoostMyShop\AdvancedStock\Model\Warehouse\ProductsImportHandler');
                $count = $importHandler->importFromCsvFile($stockId, $fullPath);
                $this->messageManager->addSuccess(__('Csv file has been imported : %1 row(s) processed', $count));
            }

        }
        catch(\Exception $ex)
        {
            //nothing
            $this->messageManager->addError(__('An error occured during import : %1', $ex->getMessage()));
        }

    }
}
