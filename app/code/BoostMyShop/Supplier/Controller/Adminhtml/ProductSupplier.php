<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml;

abstract class ProductSupplier extends \Magento\Backend\App\AbstractAction
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    protected $_resultLayoutFactory;
    protected $_backendAuthSession;
    protected $_config;
    protected $_notification;
    protected $_supplierFactory;
    protected $_supplierProductFactory;
    protected $_uploaderFactory;
    protected $_dir;
    protected $_resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \BoostMyShop\Supplier\Model\SupplierFactory $supplierFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \BoostMyShop\Supplier\Model\Supplier\ProductFactory $supplierProductFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_supplierFactory = $supplierFactory;
        $this->_supplierProductFactory = $supplierProductFactory;
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
