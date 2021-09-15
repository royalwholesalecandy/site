<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Invoice;

use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;

class AssociateOrder extends \BoostMyShop\Supplier\Controller\Adminhtml\Invoice
{

    public function execute()
    {   
        $this->_initAction();
        if ($this->getRequest()->isAjax()) 
        {
            $data = $this->getRequest()->getPost();
            $id = $this->getRequest()->getParam('bsi_id');
            $model = $this->_invoiceFactory->create()->load($id);

            $orderId = $data['po_id'];
            $amount = $data['po_total'];
            if($orderId && $orderId > 0 && $amount && $amount > 0){
                $model->linkOrder($orderId, $amount);
                $model->save();
            }
            $this->_coreRegistry->register('current_supplier_invoice', $model);
            $layout = $this->layoutFactory->create();
            $html = $layout->createBlock('BoostMyShop\Supplier\Block\Invoice\Edit\Tab\Po')
                ->toHtml();
            $this->_translateInline->processResponseBody($html);
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($html);
            return $resultJson;
        }
        
    }
}