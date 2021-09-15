<?php


namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class MassPrepare extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
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
        $orderIds = $this->getRequest()->getPost('massaction');

        $errorCount = 0;
        $successCount = 0;

        $userId = $this->_preparationRegistry->getCurrentOperatorId();
        $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();
        foreach($orderIds as $orderId)
        {
            try
            {
                $order = $this->_orderFactory->create()->load($orderId);
                $this->_orderPreparationFactory->create()->addOrder($order, [], $userId, $warehouseId);
                $successCount++;
            }
            catch(\Exception $ex)
            {
                $errorCount++;
            }
        }


        $this->messageManager->addSuccess(__('%1 orders added to the progress list.', $successCount));
        if ($errorCount > 0)
            $this->messageManager->addError(__('%1 orders have not been added.', $errorCount));

        $this->_redirect('*/*/index');

    }
}
