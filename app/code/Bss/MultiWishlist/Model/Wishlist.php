<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiWishlist\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Wishlist\Model\ResourceModel\Wishlist as ResourceWishlist;
use Magento\Wishlist\Model\ResourceModel\Wishlist\Collection;

/**
 * Wishlist model
 *
 * @method \Magento\Wishlist\Model\ResourceModel\Wishlist _getResource()
 * @method \Magento\Wishlist\Model\ResourceModel\Wishlist getResource()
 * @method int getShared()
 * @method \Magento\Wishlist\Model\Wishlist setShared(int $value)
 * @method string getSharingCode()
 * @method \Magento\Wishlist\Model\Wishlist setSharingCode(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Wishlist\Model\Wishlist setUpdatedAt(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Wishlist extends \Magento\Wishlist\Model\Wishlist
{
    /**
     * Add catalog product object data to wishlist
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $qty
     * @param bool $forciblySetQty
     * @param bool $forceNewItem
     * @param int $multiWishlistId
     * @return \Magento\Wishlist\Model\Item|null
     */
    protected function _addCatalogProduct(
        \Magento\Catalog\Model\Product $product,
        $qty = 1,
        $forciblySetQty = false,
        $forceNewItem = false,
        $multiWishlistId = 0
    ) {
        $item = null;
        if ($multiWishlistId !== null) {
            foreach ($this->getItemCollection() as $_item) {
                if ($_item->representProduct($product)) {
                   if ($_item->getMultiWishlistId() == $multiWishlistId) {
                        $item = $_item;
                        break;
                   }
                }
            }
        }else{
            foreach ($this->getItemCollection() as $_item) {
                if ($_item->representProduct($product)) {
                    $item = $_item;
                    break;
                }
            }
        }

        if ($item === null || $forceNewItem == true) {
            $storeId = $product->hasWishlistStoreId() ? $product->getWishlistStoreId() : $this->getStore()->getId();
            $item = $this->_wishlistItemFactory->create();
            $item->setProductId($product->getId());
            $item->setWishlistId($this->getId());
            $item->setAddedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));
            $item->setStoreId($storeId);
            $item->setOptions($product->getCustomOptions());
            $item->setProduct($product);
            $item->setQty($qty);
            $item->setMultiWishlistId($multiWishlistId);
            $item->save();
            if ($item->getId()) {
                $this->getItemCollection()->addItem($item);
            }
        } else {
            $qty = $forciblySetQty ? $qty : $item->getQty() + $qty;
            $item->setQty($qty)->save();
        }

        $this->addItem($item);

        return $item;
    }


    /**
     * Adds new product to wishlist.
     * Returns new item or string on error.
     *
     * @param int|\Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject|array|string|null $buyRequest
     * @param bool $forciblySetQty
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return Item|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */

    /**
     * @param int|\Magento\Catalog\Model\Product $product
     * @param null $buyRequest
     * @param bool $forciblySetQty
     * @param bool $forceNewItem
     * @param null $multiWishlistId
     * @return \Magento\Wishlist\Model\Item|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addNewItem(
        $product,
        $buyRequest = null,
        $forciblySetQty = false,
        $forceNewItem = false,
        $multiWishlistId = null
    ) {
        /*
         * Always load product, to ensure:
         * a) we have new instance and do not interfere with other products in wishlist
         * b) product has full set of attributes
         */
        if ($product instanceof \Magento\Catalog\Model\Product) {
            $productId = $product->getId();
            // Maybe force some store by wishlist internal properties
            $storeId = $product->hasWishlistStoreId() ? $product->getWishlistStoreId() : $product->getStoreId();
        } else {
            $productId = (int)$product;
            if (isset($buyRequest) && $buyRequest->getStoreId()) {
                $storeId = $buyRequest->getStoreId();
            } else {
                $storeId = $this->_storeManager->getStore()->getId();
            }
        }

        try {
            $product = $this->productRepository->getById($productId, false, $storeId);
        } catch (NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Cannot specify product.'));
        }

        if ($buyRequest instanceof \Magento\Framework\DataObject) {
            $_buyRequest = $buyRequest;
        } elseif (is_string($buyRequest)) {
            $_buyRequest = new \Magento\Framework\DataObject(unserialize($buyRequest));
        } elseif (is_array($buyRequest)) {
            $_buyRequest = new \Magento\Framework\DataObject($buyRequest);
        } else {
            $_buyRequest = new \Magento\Framework\DataObject();
        }

        if ($product->getTypeId() == "grouped") {
            if (isset($_buyRequest['super_attribute'])) {
                unset($_buyRequest['super_attribute']);
            }
        }

        /* @var $product \Magento\Catalog\Model\Product */
        $cartCandidates = $product->getTypeInstance()->processConfiguration($_buyRequest, clone $product);

        /**
         * Error message
         */
        if (is_string($cartCandidates)) {
            return $cartCandidates;
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($cartCandidates)) {
            $cartCandidates = [$cartCandidates];
        }

        $errors = [];
        $items = [];

        foreach ($cartCandidates as $candidate) {
            if ($candidate->getParentProductId()) {
                continue;
            }
            $candidate->setWishlistStoreId($storeId);

            $qty = $candidate->getQty() ? $candidate->getQty() : 1;
            // No null values as qty. Convert zero to 1.
            $item = $this->_addCatalogProduct($candidate, $qty, $forciblySetQty, $forceNewItem, $multiWishlistId);
            $items[] = $item;

            // Collect errors instead of throwing first one
            if ($item->getHasError()) {
                $errors[] = $item->getMessage();
            }
        }

        $this->_eventManager->dispatch('wishlist_product_add_after', ['items' => $items]);

        return $item;
    }

    /**
     * @param int|\Magento\Wishlist\Model\Item $itemId
     * @param \Magento\Framework\DataObject $buyRequest
     * @param null $params
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateItem($itemId, $buyRequest, $params = null)
    {
        $item = null;
        if ($itemId instanceof Item) {
            $item = $itemId;
        } else {
            $item = $this->getItem((int)$itemId);
        }
        if (!$item) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t specify a wish list item.'));
        }

        $product = $item->getProduct();
        $productId = $product->getId();
        if ($productId) {
            if (!$params) {
                $params = new \Magento\Framework\DataObject();
            } elseif (is_array($params)) {
                $params = new \Magento\Framework\DataObject($params);
            }
            $params->setCurrentConfig($item->getBuyRequest());
            $buyRequest = $this->_catalogProduct->addParamsToBuyRequest($buyRequest, $params);

            $product->setWishlistStoreId($item->getStoreId());
            $items = $this->getItemCollection();
            $isForceSetQuantity = true;
            foreach ($items as $_item) {
                /* @var $_item Item */
                if ($_item->getProductId() == $product->getId() && $_item->representProduct(
                    $product
                ) && $_item->getId() != $item->getId() && $_item->getMultiWishlistId() != $item->getMultiWishlistId()
                ) {
                    // We do not add new wishlist item, but updating the existing one
                    $isForceSetQuantity = false;
                }
            }
            $resultItem = $this->addNewItem($product, $buyRequest, $isForceSetQuantity, false, $item->getMultiWishlistId());
            /**
             * Error message
             */
            if (is_string($resultItem)) {
                throw new \Magento\Framework\Exception\LocalizedException(__($resultItem));
            }

            if ($resultItem->getId() != $itemId) {
                if ($resultItem->getDescription() != $item->getDescription()) {
                    $resultItem->setDescription($item->getDescription())->save();
                }
                $item->isDeleted(true);
                $this->setDataChanges(true);
            } else {
                $resultItem->setQty($buyRequest->getQty() * 1);
                $resultItem->setOrigData('qty', 0);
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('The product does not exist.'));
        }
        return $this;
    }
}
