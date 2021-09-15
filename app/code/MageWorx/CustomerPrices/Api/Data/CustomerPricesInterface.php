<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */


namespace MageWorx\CustomerPrices\Api\Data;

interface CustomerPricesInterface
{

    const ENTITY_ID           = 'entity_id';
    const ATTRIBUTE_TYPE      = 'attribute_type';
    const CUSTOMER_ID         = 'customer_id';
    const PRODUCT_ID          = 'product_id';
    const PRICE               = 'price';
    const PRICE_TYPE          = 'price_type';
    const SPECIAL_PRICE       = 'special_price';
    const SPECIAL_PRICE_TYPE  = 'special_price_type';
    const DISCOUNT            = 'discount';
    const DISCOUNT_PRICE_TYPE = 'discount_price_type';
    const PRICE_VALUE         = 'price_value';
    const SPECIAL_PRICE_VALUE = 'special_price_value';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getAttributeType();

    /**
     * @return int|null
     */
    public function getCustomerId();

    /**
     * @return int|null
     */
    public function getProductId();

    /**
     * @return string|null
     */
    public function getPrice();

    /**
     * @return int|null
     */
    public function getPriceType();

    /**
     * @return string|null
     */
    public function getSpecialPrice();

    /**
     * @return int|null
     */
    public function getSpecialPriceType();

    /**
     * @return string|null
     */
    public function getDiscount();

    /**
     * @return int|null
     */
    public function getDiscountPriceType();

    /**
     * @param int
     * @return $this
     */
    public function setId($id);

    /**
     * @param int
     * @return $this
     */
    public function setAttributeType($AttributeType);

    /**
     * @param int
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * @param int
     * @return $this
     */
    public function setProductId($productId);

    /**
     * @param string
     * @return $this
     */
    public function setPrice($customerPrice);

    /**
     * @param int
     * @return $this
     */
    public function setPriceType($priceType);

    /**
     * @param string
     * @return $this
     */
    public function setSpecialPrice($customerSpecialPrice);

    /**
     * @param int
     * @return $this
     */
    public function setSpecialPriceType($specialPriceType);

    /**
     * @param string
     * @return $this
     */
    public function setDiscount($customerDiscount);

    /**
     * @param int
     * @return $this
     */
    public function setDiscountPriceType($discountPriceType);

    /**
     * @return string|null
     */
    public function getSpecialPriceValue();

    /**
     * @param string $specialValue
     * @return $this
     */
    public function setSpecialPriceValue($specialValue);

    /**
     * @return string|null
     */
    public function getPriceValue();

    /**
     * @param string $specialValue
     * @return $this
     */
    public function setPriceValue($specialValue);
}
