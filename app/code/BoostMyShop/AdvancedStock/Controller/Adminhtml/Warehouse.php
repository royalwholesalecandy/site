<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml;

abstract class Warehouse extends \Magento\Backend\App\AbstractAction
{

    protected $_coreRegistry;
    protected $_warehouseFactory;
    protected $_resultLayoutFactory;
    protected $_fileFactory;
    protected $_product;
    protected $_uploaderFactory;
    protected $_httpFactory;
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
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_warehouseFactory = $warehouseFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_fileFactory = $fileFactory;
        $this->_product = $product;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_httpFactory = $httpFactory;
        $this->_dir = $dir;
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
