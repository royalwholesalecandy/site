<?php


namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class PrintAction extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
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

        $poId = $this->getRequest()->getParam('po_id');
        if ($poId) {
            $model = $this->_orderFactory->create();
            $po = $model->load($poId);
            if ($po) {
                $pdf = $this->_objectManager->create('BoostMyShop\Supplier\Model\Pdf\Order')->getPdf([$po]);
                $date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
                return $this->_objectManager->get('\Magento\Framework\App\Response\Http\FileFactory')->create(
                    'purchase_order' . $date . '.pdf',
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
