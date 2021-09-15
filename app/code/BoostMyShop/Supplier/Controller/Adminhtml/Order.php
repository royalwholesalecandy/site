<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml;

abstract class Order extends \Magento\Backend\App\AbstractAction
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * User model factory
     *
     * @var \Magento\User\Model\UserFactory
     */
    protected $_orderFactory;
    protected $_orderCollectionFactory;
    protected $_receptionFactory;
    protected $_orderProductFactory;
    protected $_csvImport;
    protected $_fileImport;
    protected $_resultLayoutFactory;
    protected $_backendAuthSession;
    protected $_config;
    protected $_notification;
    protected $_timezoneInterface;
    protected $_uploaderFactory;
    protected $_importHandler;
    protected $_httpFactory;
    protected $_dir;
    protected $_product;
    protected $_supplierProductFactory;
    protected $_dateFilter;
    protected $_supplierFactory;

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
        \BoostMyShop\Supplier\Model\Config $config,
        \BoostMyShop\Supplier\Model\Order\Notification $notification,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \BoostMyShop\Supplier\Model\OrderFactory $orderFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \BoostMyShop\Supplier\Model\Order\ReceptionFactory $receptionFactory,
        \BoostMyShop\Supplier\Model\Order\ProductFactory $orderProductFactory,
        \BoostMyShop\Supplier\Model\Order\CsvImport $csvImport,
        \BoostMyShop\Supplier\Model\Order\FileExport $fileExport,
        \BoostMyShop\Supplier\Model\Product $product,
        \BoostMyShop\Supplier\Model\SupplierFactory $supplierFactory,
        \BoostMyShop\Supplier\Model\Order\ProductsImportHandler $importHandler,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \BoostMyShop\Supplier\Model\Supplier\ProductFactory $supplierProductFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_orderFactory = $orderFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_orderProductFactory = $orderProductFactory;
        $this->_csvImport = $csvImport;
        $this->_fileExport = $fileExport;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_config = $config;
        $this->_notification = $notification;
        $this->_importHandler = $importHandler;
        $this->_timezoneInterface = $timezoneInterface;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_httpFactory = $httpFactory;
        $this->_dir = $dir;
        $this->_product = $product;
        $this->_fileFactory = $fileFactory;
        $this->_supplierProductFactory = $supplierProductFactory;
        $this->_receptionFactory = $receptionFactory;
        $this->_dateFilter = $dateFilter;
        $this->_supplierFactory = $supplierFactory;
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
