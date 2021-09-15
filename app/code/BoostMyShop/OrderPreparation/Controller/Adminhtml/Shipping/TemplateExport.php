<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Shipping;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class TemplateExport extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Shipping
{

    /**
     * @return void
     */
    public function execute()
    {
        try
        {
            $templateId = $this->getRequest()->getParam('ct_id');
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
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__($ex->getMessage()));
            $this->_redirect('*/*/index');
        }

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

}
