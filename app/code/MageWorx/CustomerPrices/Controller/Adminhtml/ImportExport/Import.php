<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\CustomerPrices\Controller\Adminhtml\ImportExport;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Import extends \MageWorx\CustomerPrices\Controller\Adminhtml\ImportExport
{
    /**
     * Import action from import/export customer prices
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                /** @var \MageWorx\CustomerPrices\Api\ImportHandlerInterface $importHandler */
                $importHandler = $this->importHandlerFactory->create();
                $importHandler->importFromCsvFile($this->getRequest()->getFiles('import_customerprices_file'));

                $this->messageManager->addSuccessMessage(__('Data has been imported.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage(__('Invalid file upload attempt'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());

        return $resultRedirect;
    }
}
