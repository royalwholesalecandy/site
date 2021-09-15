<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml;

abstract class Invoice extends \Magento\Backend\App\AbstractAction
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
    protected $_invoiceFactory;
    protected $_invoiceOrderFactory;
    protected $_resultLayoutFactory;
    protected $_backendAuthSession;
    protected $_config;
    protected $_timezoneInterface;
    protected $_fileFactory;
    protected $filesystem;
    protected $uploaderFactory;
    protected $_dateFactory;
    protected $layoutFactory;
    protected $_translateInline;
    protected $date;
    protected $_file;
    protected $_dateFilter;

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
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \BoostMyShop\Supplier\Model\InvoiceFactory $invoiceFactory,
        \BoostMyShop\Supplier\Model\Invoice\OrderFactory $invoiceOrderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Filesystem\Driver\File $file
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_invoiceFactory = $invoiceFactory;
        $this->_invoiceOrderFactory = $invoiceOrderFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_config = $config;
        $this->_timezoneInterface = $timezoneInterface;
        $this->filesystem = $filesystem;
        $this->_fileFactory = $fileFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->_dateFactory = $dateFactory;
        $this->layoutFactory = $layoutFactory;
        $this->_translateInline = $translateInline;
        $this->date = $date;
        $this->_file = $file;
        $this->_dateFilter = $dateFilter;
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
