<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml;

abstract class Routing extends \Magento\Backend\App\AbstractAction
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    protected $_resultLayoutFactory;

    protected $_routingStoreFactory;
    protected $_routingStoreWarehouse;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \BoostMyShop\AdvancedStock\Model\Routing\StoreFactory $routingStore,
        \BoostMyShop\AdvancedStock\Model\Routing\Store\WarehouseFactory $routingWarehouse
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_routingStoreFactory = $routingStore;
        $this->_routingStoreWarehouse = $routingWarehouse;
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
