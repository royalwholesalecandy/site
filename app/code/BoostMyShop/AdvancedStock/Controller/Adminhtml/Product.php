<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml;

abstract class Product extends \Magento\Backend\App\AbstractAction
{

    protected $_coreRegistry;
    protected $_resultLayoutFactory;
    protected $_fileFactory;
    protected $_productFactory;
    protected $_uploaderFactory;
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
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_fileFactory = $fileFactory;
        $this->_productFactory = $productFactory;
        $this->_uploaderFactory = $uploaderFactory;
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
