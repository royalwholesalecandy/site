<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use Magento\Framework\Controller\ResultFactory;

class Tab extends \Magento\Backend\App\AbstractAction
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
        $this->_coreRegistry->register('advancedstock_current_product', $product);

        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);

        return $resultLayout;
    }

    protected function _isAllowed()
    {
        return true;
    }

}
