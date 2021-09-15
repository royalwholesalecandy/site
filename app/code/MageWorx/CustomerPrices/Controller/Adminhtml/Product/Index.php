<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */


namespace MageWorx\CustomerPrices\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;

class Index extends \Magento\Catalog\Controller\Adminhtml\Product
{
    protected $resultLayoutFactory;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $product = $this->productBuilder->build($this->getRequest());
        $resultLayout = $this->resultLayoutFactory->create();
        $block = $resultLayout->getLayout()->getBlock('product.edit.tab.customerprices.grid');
        $block->setProductId($product->getId());
        return $resultLayout;
    }

    protected function _isAllowed()
    {
        return true;
    }
}
