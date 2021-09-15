<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\MassStockEditor;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportProductsCsv extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse
{

    /**
     * @return void
     */
    public function execute()
    {

        $fileName = 'mass_stock_editor.csv';
        $content = $this->_view->getLayout()->createBlock(
            'BoostMyShop\AdvancedStock\Block\MassStockEditor\Grid'
        )->getCsv();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
