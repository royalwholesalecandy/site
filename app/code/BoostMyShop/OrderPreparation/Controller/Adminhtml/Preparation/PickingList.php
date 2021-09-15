<?php


namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class PickingList extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
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
        $this->_auth->getAuthStorage()->setIsFirstPageAfterLogin(false);

        $userId = $this->_preparationRegistry->getCurrentOperatorId();
        $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();
        $orders = $this->_objectManager->get('\BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory')
                            ->create()
                            ->addOrderDetails()
                            ->addWarehouseFilter($warehouseId)
                            ->addUserFilter($userId);

        if (count($orders) > 0)
        {
            try{
                $obj = $this->_objectManager->create('BoostMyShop\OrderPreparation\Model\Pdf\PickingList');
                $pdf = $obj->getPdf($orders);
                $date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
                $fileName = 'picking_' . $date . '.pdf';

                //download file
                $content = $this->_objectManager->get('\Magento\Framework\App\Response\Http\FileFactory')->create(
                    $fileName,
                    $pdf->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );

                //delete file
                $dir = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
                $dir->delete($fileName);

            }catch(\Exception $e){
                $this->messageManager->addWarningMessage($e->getMessage());
                $this->_redirect('*/*/index');
            }
}
        else
        {
            $this->messageManager->addError(__('There is no order in progress.'));
            $this->_redirect('*/*/index');
        }

    }
}
