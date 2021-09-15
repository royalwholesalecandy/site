<?php
/**
 *
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Api\Data;

interface CustomerGroupPricesInterface
{
    const ENTITY_ID           = 'entity_id';
    const PRODUCT_ID          = 'product_id';
    const GROUP_ID            = 'group_id';
    const PRICE               = 'price';
    const WEBSITE_ID          = 'website_id';
    const PRICE_TYPE          = 'price_type';
    const ABSOLUTE_PRICE_TYPE = 'absolute_price_type';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int
     *
     * @return $this
     */
    public function setId($id);

    /**
     * @return int|null
     */
    public function getProductId();

    /**
     * @param int
     *
     * @return $this
     */
    public function setProductId($productId);

    /**
     * @return int|null
     */
    public function getGroupId();

    /**
     * @param int
     *
     * @return $this
     */
    public function setGroupId($groupId);

    /**
     * @return string|null
     */
    public function getPrice();

    /**
     * @param string
     *
     * @return $this
     */
    public function setPrice($price);

    /**
     * @return int|null
     */
    public function getWebsiteId();

    /**
     * @param int
     *
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * @return int|null
     */
    public function getPriceType();

    /**
     * @param int
     *
     * @return $this
     */
    public function setPriceType($priceType);

    /**
     * @return int|null
     */
    public function getAbsolutePriceType();

    /**
     * @param int
     *
     * @return $this
     */
    public function setAbsolutePriceType($absolutePriceType);
}
