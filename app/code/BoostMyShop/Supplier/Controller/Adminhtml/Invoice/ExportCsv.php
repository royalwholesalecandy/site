<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Invoice;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \BoostMyShop\Supplier\Controller\Adminhtml\Invoice
{

    /**
     * @return void
     */
    public function execute()
    {

        $this->_initAction();
        $fileName = 'supplier_invoice.csv';
        $content = $this->_view->getLayout()->createBlock('\BoostMyShop\Supplier\Block\Invoice\Grid');
        
        return $this->_fileFactory->create(
            $fileName,
            $content->getCsvFile($fileName),
            DirectoryList::VAR_DIR
        );
    }
}
