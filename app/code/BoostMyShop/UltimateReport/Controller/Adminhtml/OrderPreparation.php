<?php

namespace BoostMyShop\UltimateReport\Controller\Adminhtml;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;

abstract class OrderPreparation extends \Magento\Backend\App\AbstractAction
{

    protected $_ultimateReportRegistry;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \BoostMyShop\UltimateReport\Model\Registry $ultimateReportRegistry,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory

    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_resultRawFactory = $resultRawFactory;
        $this->_resultPageFactory = $resultPageFactory;

        $this->_ultimateReportRegistry = $ultimateReportRegistry;
    }

    protected function _initAction()
    {
        $this->_view->loadLayout();

        return $this;
    }

    protected function _isAllowed()
    {
        return true;
    }
}
