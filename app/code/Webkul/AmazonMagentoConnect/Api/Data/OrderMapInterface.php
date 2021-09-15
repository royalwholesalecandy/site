<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Api\Data;

interface OrderMapInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const AMAZON_ORDER_ID = 'amazon_order_id';
    const MAGE_AMZ_ACCOUNT_ID = 'mage_amz_account_id';
    const MAGE_ORDER_ID = 'mage_order_id';
    const STATUS = 'status';
    const CREATED_AT = 'created';
    const PURCHASE_DATE = 'purchase_date';

    /**
     * Get ID.
     * @return int|null
     */
    public function getId();

    /**
     * set ID.
     * @return $this
     */
    public function setId($id);

    /**
     * Get amazon order id.
     * @return string
     */
    public function getAmazonOrderId();

    /**
     * set amazon order id.
     * @return $this
     */
    public function setAmazonOrderId($amazonOrderId);

    /**
     * Get MageAmzAccountId.
     * @return string
     */
    public function getMageAmzAccountId();

    /**
     * set amzAccountId.
     * @return $this
     */
    public function setMageAmzAccountId($amzAccountId);

    /**
     * Get MageOrderId.
     * @return string
     */
    public function getMageOrderId();

    /**
     * set MageOrderId.
     * @return $this
     */
    public function setMageOrderId($mageOrderId);

    /**
     * Get CreatedAt.
     * @return string
     */
    public function getCreatedAt();

    /**
     * set CreatedAt.
     * @return $this
     */
    public function setCreatedAt($created);

    /**
     * Get PurchaseDate.
     * @return string
     */
    public function getPurchaseDate();

    /**
     * set PurchaseDate.
     * @return $this
     */
    public function setPurchaseDate($purchaseDate);
}
