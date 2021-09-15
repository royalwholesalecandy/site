<?php


namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class MassRemove extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
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
        $inProgressIds = $this->getRequest()->getPost('massaction');

        $successCount = 0;
        $errorCount = 0;

        foreach($inProgressIds as $inProgressId)
        {
            try
            {
                $inProgress = $this->_inProgressFactory->create()->load($inProgressId);
                $inProgress->delete();

                $successCount++;
            }
            catch(\Exception $ex)
            {
                $errorCount++;
            }
        }


        $this->messageManager->addSuccess(__('%1 orders removed from the progress list.', $successCount));
        if ($errorCount > 0)
            $this->messageManager->addError(__('%1 orders have not been removed.', $errorCount));

        $this->_redirect('*/*/index');

    }
}

