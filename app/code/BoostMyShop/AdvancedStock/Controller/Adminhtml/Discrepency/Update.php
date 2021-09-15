<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Discrepency;

class Update extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Discrepency
{
    public function execute()
    {
        try
        {
            $fix = ($this->getRequest()->getParam('fix') == 1);
            $this->_stockDiscrepencies->run($fix);

            if ($fix)
                $this->messageManager->addSuccess(__('Fixes applied, please update report.'));
            else
                $this->messageManager->addSuccess(__('Report updated.'));

        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__('An error occured : %1 : %2.', $ex->getMessage(), $ex->getTraceAsString()));
        }

        $this->_redirect('*/*/report');
    }

}
