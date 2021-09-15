<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Controller\Adminhtml\Product;

class CustomerSearchGrid extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * Search for attributes by part of attribute's label in admin store
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('mageworx.customerprices.customersearch.grid');
        return $resultLayout;
    }

    protected function _isAllowed()
    {
        return true;
    }
}
