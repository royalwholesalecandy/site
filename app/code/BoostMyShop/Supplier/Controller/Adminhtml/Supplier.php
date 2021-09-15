<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml;

abstract class Supplier extends \Magento\Backend\App\AbstractAction
{
    protected $_coreRegistry;
    protected $_supplierFactory;
    protected $_supplierProductFactory;
    protected $_uploaderFactory;
    protected $_resultLayoutFactory;
    protected $_dir;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \BoostMyShop\Supplier\Model\SupplierFactory $supplierFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \BoostMyShop\Supplier\Model\Supplier\ProductFactory $supplierProductFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_supplierFactory = $supplierFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;
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
