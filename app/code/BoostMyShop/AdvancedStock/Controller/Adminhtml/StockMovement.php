<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml;

abstract class StockMovement extends \Magento\Backend\App\AbstractAction
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    protected $_stockMovementFactory;

    protected $_resultLayoutFactory;

    protected $_stockMovementLogsFactory;

    protected $_fileFactory;



    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory,
        \BoostMyShop\AdvancedStock\Model\StockMovementLogsFactory $stockMovementLogsFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory


    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_stockMovementFactory = $stockMovementFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_stockMovementLogsFactory = $stockMovementLogsFactory;
        $this->_fileFactory = $fileFactory;

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
