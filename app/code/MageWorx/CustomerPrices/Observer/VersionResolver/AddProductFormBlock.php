<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\CustomerPrices\Observer\VersionResolver;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\ProductMetadataInterface;

class AddProductFormBlock implements ObserverInterface
{
    /**
     * @var ProductMetadataInterface $productMetadata
     */
    public $productMetadata;

    /**
     * AddProductFormBlock constructor.
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ProductMetadataInterface $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
    }

    /**
     * Add Custom Prices to product
     *
     * @param EventObserver $observer
     * @return $this|void
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->isAddLayoutUpdate()) {
            return $this;
        }

        /** @var string $fullActionName */
        $fullActionName = $observer->getEvent()->getFullActionName();
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $observer->getEvent()->getLayout();
        /** @var \Magento\Framework\View\Layout\ProcessorInterface $update */
        $update = $layout->getUpdate();
        $handles = $update->getHandles();

        foreach ($handles as $handle) {
            $update->addHandle($handle);
            if (in_array($handle, ['catalog_product_edit', 'catalog_product_new'])) {
                $update->addHandle('catalog_product_new_no_ui');
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function isAddLayoutUpdate()
    {
        return  version_compare($this->productMetadata->getVersion(), '2.2.0-dev', '<');
    }
}
