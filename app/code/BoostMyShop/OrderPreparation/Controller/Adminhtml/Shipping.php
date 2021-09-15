<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml;

abstract class Shipping extends \Magento\Backend\App\AbstractAction
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    protected $_resultLayoutFactory;
    protected $_layoutFactory;
    protected $_backendAuthSession;
    protected $_orderPreparationFactory;
    protected $_orderFactory;
    protected $_invoicePdf;
    protected $_shipmentPdf;
    protected $_invoiceCollectionFactory;
    protected $_shipmentCollectionFactory;
    protected $_configFactory = null;
    protected $_preparationRegistry;
    protected $_inProgressFactory;
    protected $_templateFactory;
    protected $_dir;
    protected $_uploaderFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \BoostMyShop\OrderPreparation\Model\OrderPreparationFactory $orderPreparationFactory,
        \BoostMyShop\OrderPreparation\Model\InProgressFactory $inProgressFactory,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory $inProgressCollectionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \BoostMyShop\OrderPreparation\Model\ConfigFactory $configFactory,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplateFactory $templateFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_preparationRegistry = $preparationRegistry;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_layoutFactory = $layoutFactory;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_orderPreparationFactory = $orderPreparationFactory;
        $this->_orderFactory = $orderFactory;
        $this->_configFactory = $configFactory;
        $this->_inProgressFactory = $inProgressFactory;
        $this->_templateFactory = $templateFactory;
        $this->_inProgressCollectionFactory = $inProgressCollectionFactory;
        $this->_dir = $dir;
        $this->_uploaderFactory = $uploaderFactory;

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
