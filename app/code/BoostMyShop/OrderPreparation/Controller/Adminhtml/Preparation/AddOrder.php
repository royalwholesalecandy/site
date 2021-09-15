<?php


namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class AddOrder extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
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
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->_orderFactory->create()->load($orderId);
        $userId = $this->_preparationRegistry->getCurrentOperatorId();
        $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();

        try
        {
            $this->_orderPreparationFactory->create()->addOrder($order, [], $userId, $warehouseId);
            $this->messageManager->addSuccess(__('Order added to in progress list.'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError($ex->getMessage());
        }

        $this->_redirect('*/*/index');

    }
}
