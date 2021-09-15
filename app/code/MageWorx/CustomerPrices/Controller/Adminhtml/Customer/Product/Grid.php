<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Controller\Adminhtml\Customer\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use MageWorx\CustomerPrices\Block\Adminhtml\Customer\Edit\Tab\CustomerPrice as CustomerPrice;

class Grid extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     * @param ProductBuilder $productBuilder
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory,
        ProductBuilder $productBuilder
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory    = $layoutFactory;
    }

    /**
     * @return string
     */
    public function execute()
    {
        /* no possibility to use existing layout from _view because of uncertain mistake call reload page */
        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                CustomerPrice::class,
                'customer.products.grid'
            )->toHtml()
        );
    }
}