<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Model\Edit;

class Thumbnail
{
    /**
     * Order Editor helper
     *
     * @var \MageWorx\OrderEditor\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepositoryInterface;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * Thumbnail constructor.
     *
     * @param \MageWorx\OrderEditor\Helper\Data $helperData
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param \Magento\Catalog\Helper\Image $imageHelper
     */
    public function __construct(
        \MageWorx\OrderEditor\Helper\Data $helperData,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Catalog\Helper\Image $imageHelper
    ) {
        $this->helperData                 = $helperData;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->imageHelper                = $imageHelper;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return $this|\Magento\Catalog\Helper\Image|null|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getImgByItem($item)
    {
        $productId = $item->getProductId();
        try {
            $product   = $this->productRepositoryInterface->getById($productId, false, $item->getStoreId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e){
            $productId = '';
        }
		if($productId){
        $product   = $this->productRepositoryInterface->getById($productId, false, $item->getStoreId());

        switch ($product->getTypeId()) {
            case 'configurable':
                return $this->getImgByItemForConfigurableProduct($item, $product);

            default:
                if ($product->getThumbnail() && $product->getThumbnail() != 'no_selection') {
                    try {
                        return $this->imageHelper->init($product, 'product_listing_thumbnail');
                    } catch (\Exception $e) {
                        return;
                    }
                }
        }
		}
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @param \Magento\Catalog\Model\Product $product
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getImgByItemForConfigurableProduct($item, $product)
    {
        $product = $this->productRepositoryInterface->get($item->getSku());
        if (!$product->getId()) {
            return;
        }

        if ($product->getThumbnail() && $product->getThumbnail() != 'no_selection') {
            try {
                return $this->imageHelper->init($product, 'product_listing_thumbnail');
            } catch (\Exception $e) {
                return;
            }
        } else {
            if ($product->getThumbnail() && $product->getThumbnail() != 'no_selection') {
                try {
                    return $this->imageHelper->init($product, 'product_listing_thumbnail');
                } catch (\Exception $e) {
                    return;
                }
            }
        }
    }
}
