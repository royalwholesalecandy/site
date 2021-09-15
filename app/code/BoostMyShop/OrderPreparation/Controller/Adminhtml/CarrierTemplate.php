<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml;

abstract class CarrierTemplate extends \Magento\Backend\App\AbstractAction
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    protected $_resultLayoutFactory;
    protected $_backendAuthSession;
    protected $_configFactory = null;
    protected $_carrierTemplateFactory = null;


    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplateFactory $carrierTemplateFactory,
        \BoostMyShop\OrderPreparation\Model\ConfigFactory $configFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_configFactory = $configFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_carrierTemplateFactory = $carrierTemplateFactory;
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();


        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
