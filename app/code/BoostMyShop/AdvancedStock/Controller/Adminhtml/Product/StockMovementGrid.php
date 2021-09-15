<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use Magento\Framework\Controller\ResultFactory;

class StockMovementGrid extends \Magento\Backend\App\AbstractAction
{

    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        ProductBuilder $productBuilder
    ) {
        $this->productBuilder = $productBuilder;
        $this->_coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    public function execute()
    {
        $product = $this->productBuilder->build($this->getRequest());

        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    protected function _isAllowed()
    {
        return true;
    }

}
