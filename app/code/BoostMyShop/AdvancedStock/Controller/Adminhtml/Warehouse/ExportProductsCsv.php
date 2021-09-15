<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportProductsCsv extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse
{

    /**
     * @return void
     */
    public function execute()
    {
        //prevent magento to redirect to dashboard
        $this->_auth->getAuthStorage()->setIsFirstPageAfterLogin(false);

        $warehouseId = $this->getRequest()->getParam('w_id');
        $model = $this->_warehouseFactory->create()->load($warehouseId);
        $this->_coreRegistry->register('current_warehouse', $model);

        $fileName = 'warehouse_products.csv';
        $content = $this->_view->getLayout()->createBlock(
            'BoostMyShop\AdvancedStock\Block\Warehouse\Edit\Tab\Products'
        )->getCsv();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
