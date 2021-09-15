<?php

namespace BoostMyShop\UltimateReport\Controller\Adminhtml\Page;

class Index extends \BoostMyShop\UltimateReport\Controller\Adminhtml\Page
{

    /**
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if (isset($data['filter']))
            $this->_ultimateReportRegistry->setRegistry('filters', $data['filter']);

        $page = $data['page'];
        $layoutCode = 'reports_'.$page;

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->addHandle($layoutCode);
        $result = $resultPage->getLayout()->renderElement('content');

        return $this->_resultRawFactory->create()->setContents($result);
    }

}
