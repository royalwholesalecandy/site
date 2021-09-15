<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Payments;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;

class ExportPaymentsCsv extends \BoostMyShop\Supplier\Controller\Adminhtml\Payments
{
    
    public function execute()
    {
        try{
            $csv = $this->_view->getLayout()->createBlock('\BoostMyShop\Supplier\Block\Payments\PaymentsGrid')->getCsv();
            $date = $this->_timezoneInterface->date()->format('Y-m-d_H');

            return $this->_fileFactory->create(
                'supplier_payments' . $date . '.csv',
                $csv,
                DirectoryList::VAR_DIR,
                'application/csv'
            );

        }catch(\Exception $e){
            $this->messageManager->addError(__('An error occurred : '.$e->getMessage()));
            $this->_redirect('*/*/index');
        }
    }
}