<?php


namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Remove extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
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
        $inProgressId = $this->getRequest()->getParam('in_progress_id');

        $this->_orderPreparationFactory->create()->remove($inProgressId);

        $this->messageManager->addSuccess(__('Order removed from progress list.'));
        $this->_redirect('*/*/index');

    }
}
