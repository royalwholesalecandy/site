<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\LowStock;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportProductsCsv extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse
{

    /**
     * @return void
     */
    public function execute()
    {

        $fileName = 'stock_helper.csv';
        $content = $this->_view->getLayout()->createBlock(
            'BoostMyShop\AdvancedStock\Block\LowStock\Grid'
        )->getCsv();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
