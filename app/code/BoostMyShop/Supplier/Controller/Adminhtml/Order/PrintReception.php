<?php


namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class PrintReception extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
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

        $receptionId = $this->getRequest()->getParam('id');
        if ($receptionId) {
            $model = $this->_receptionFactory->create();
            $reception = $model->load($receptionId);
            if ($reception) {
                $pdf = $this->_objectManager->create('BoostMyShop\Supplier\Model\Pdf\Reception')->getPdf([$reception]);
                $date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
                return $this->_objectManager->get('\Magento\Framework\App\Response\Http\FileFactory')->create(
                    'purchase_order_reception_' . $date . '.pdf',
                    $pdf->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }
        } else {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }
}
