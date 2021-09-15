<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use Magento\Framework\Controller\ResultFactory;

class CreateStockMovement extends \Magento\Backend\App\AbstractAction
{

    protected $_stockMovementFactory;
    protected $_backendAuthSession;

    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->_stockMovementFactory = $stockMovementFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_backendAuthSession = $backendAuthSession;

        parent::__construct($context);
    }

    public function execute()
    {
        $data = ($this->getRequest()->getPost('stock_movement'));
        $productId = $data['product_id'];

        try
        {
            $userId = null;
            if ($this->_backendAuthSession->getUser())
                $userId = $this->_backendAuthSession->getUser()->getId();

            $this->_stockMovementFactory->create()->create($data['product_id'],
                $data['sm_from_warehouse_id'],
                $data['sm_to_warehouse_id'],
                $data['sm_qty'],
                $data['sm_category'],
                $data['sm_comments'],
                $userId
            );

            $this->messageManager->addSuccess(__('Stock movement created'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__('An error occured : %1.', $ex->getTraceAsString()));
        }

        $this->_redirect('catalog/product/edit', ['id' => $productId, 'active_tab' => 'product-advancedstock']);
    }

    protected function _isAllowed()
    {
        return true;
    }

}
