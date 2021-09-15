<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Invoice;

use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;

class AddPayment extends \BoostMyShop\Supplier\Controller\Adminhtml\Invoice
{
    protected function _filterPostData($data)
    {
        $inputFilter = new \Zend_Filter_Input(
            ['bsip_date' => $this->_dateFilter],
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();
        return $data;
    }

    public function execute()
    {   
        $this->_initAction();
        if ($this->getRequest()->isAjax()) 
        {
            $data = $this->getRequest()->getPostValue();
            $data = $this->_filterPostData($data);

            $id = $this->getRequest()->getParam('bsi_id');
            $model = $this->_invoiceFactory->create()->load($id);

            $date = $data['bsip_date'];
            $method = $data['bsip_method'];
            $total = $data['bsip_total'];
            $notes = $data['bsip_notes'];
            if(($date && $date != '') && ($method && $method != '') && ($total && $total != '')){
                $model->addPayment($date, $method, $total, $notes);
                $model->save();
            }
            $this->_coreRegistry->register('current_supplier_invoice', $model);
            $layout = $this->layoutFactory->create();
            $html = $layout->createBlock('BoostMyShop\Supplier\Block\Invoice\Edit\Tab\Payments')
                ->toHtml();
            $this->_translateInline->processResponseBody($html);
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($html);
            return $resultJson;
        }
        
    }
}