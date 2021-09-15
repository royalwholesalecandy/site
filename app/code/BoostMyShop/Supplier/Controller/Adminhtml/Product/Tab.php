<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use Magento\Framework\Controller\ResultFactory;

class Tab extends \Magento\Backend\App\AbstractAction
{

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     */
    public function __construct(
        Context $context,
        ProductBuilder $productBuilder
    ) {
        $this->productBuilder = $productBuilder;
        parent::__construct($context);
    }

    public function execute()
    {
        $product = $this->productBuilder->build($this->getRequest());

        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $resultLayout->getLayout()->getBlock('admin.product.supplier')
            ->setProductId($product->getId())
            ->setProduct($product)
            ->setUseAjax(true);
        return $resultLayout;
    }

    protected function _isAllowed()
    {
        return true;
    }

}
