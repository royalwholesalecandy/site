<?php


namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class SaveRegistry extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
{

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $warehouseId = $this->getRequest()->getPost('warehouse_id');
        $operatorId = $this->getRequest()->getPost('operator_id');

        $this->_preparationRegistry->changeCurrentWarehouseId($warehouseId);
        $this->_preparationRegistry->changeCurrentOperatorId($operatorId);

        $this->messageManager->addSuccess(__('settings updated.'));
        $this->_redirect('*/*/index');

    }
}
