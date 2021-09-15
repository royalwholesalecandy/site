<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake;

use Magento\Framework\App\Filesystem\DirectoryList;

class CsvExport extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake {

    public function execute(){

        $id = $this->getRequest()->getParam('id');
        $stockTake = $this->_stockTakeFactory->create()->load($id);

        $csvContent = $this->_csvExport->getCsvContent($stockTake);
        $fileName = $this->_csvExport->getFileName($stockTake);
        $mimeType = $this->_csvExport->getMimeType();

        return $this->_objectManager->get('\Magento\Framework\App\Response\Http\FileFactory')->create(
            $fileName,
            $csvContent,
            DirectoryList::VAR_DIR,
            $mimeType
        );


    }

}