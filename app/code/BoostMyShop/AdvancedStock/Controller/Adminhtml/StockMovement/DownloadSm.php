<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockMovement;

use Magento\Framework\App\Filesystem\DirectoryList;

class DownloadSm extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockMovement
{

    /**
     * @return void
     */
    public function execute()
    {
        $content = '';
        $smId = $this->getRequest()->getParam('sm_id');
        if($smId){
            $logs = $this->_stockMovementLogsFactory->create()->load($smId, 'sm_id');
            $content = str_replace("\n", "\r\n", $logs->getLog());
            $fileName = 'stock_movement_log.txt';

            return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
        }

    }
}
