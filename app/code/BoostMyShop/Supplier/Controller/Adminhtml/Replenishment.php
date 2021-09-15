<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml;

abstract class Replenishment extends \Magento\Backend\App\AbstractAction
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    protected $_resultLayoutFactory;

    protected $_orderFactory;

    protected $_replenishmentFactory = null;

    protected $_config;
    protected $_supplierFactory;
    protected $_supplierProductFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \BoostMyShop\Supplier\Model\OrderFactory $orderFactory,
        \Magento\Framework\Registry $coreRegistry,
        \BoostMyShop\Supplier\Model\ReplenishmentFactory $replenishmentFactory,
        \BoostMyShop\Supplier\Model\Config $config,
        \BoostMyShop\Supplier\Model\SupplierFactory $supplierFactory,
        \BoostMyShop\Supplier\Model\Supplier\ProductFactory $supplierProductFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_orderFactory = $orderFactory;
        $this->_replenishmentFactory = $replenishmentFactory;
        $this->_config = $config;
        $this->_supplierFactory = $supplierFactory;
        $this->_supplierProductFactory = $supplierProductFactory;
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
