<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Api\Data;

interface AccountsInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const COUNTRY = 'country';
    const MARKETPLACE_ID = 'marketplace_id';
    const SELLER_ID = 'seller_id';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const CURRENCY_CODE = 'currency_code';
    const CURRENCY_RATE = 'currency_rate';

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * set ID.
     *
     * @return $this
     */
    public function setId($entityId);

   /**
    * Get Country.
    * @return string
    */
    public function getCountry();

   /**
    * set Country.
    * @return $this
    */
    public function setCountry($country);

   /**
    * Get MarketplaceId.
    * @return string
    */
    public function getMarketplaceId();

   /**
    * set MarketplaceId.
    * @return $this
    */
    public function setMarketplaceId($marketplaceId);

   /**
    * Get SellerId.
    * @return string
    */
    public function getSellerId();

   /**
    * set SellerId.
    * @return $this
    */
    public function setSellerId($sellerId);

   /**
    * Get AccessKeyId.
    * @return string
    */
    public function getAccessKeyId();

   /**
    * set AccessKeyId.
    * @return $this
    */
    public function setAccessKeyId($accessKeyId);

   /**
    * Get SecretKey.
    * @return string
    */
    public function getSecretKey();

   /**
    * set SecretKey.
    * @return $this
    */
    public function setSecretKey($secretKey);

   /**
    * Get currency code.
    * @return string
    */
    public function getCurrencyCode();

   /**
    * set currency code.
    * @return $this
    */
    public function setCurrencyCode($currencyCode);

   /**
    * Get currency code.
    * @return string
    */
    public function getCurrencyRate();

   /**
    * set currency code.
    * @return $this
    */
    public function setCurrencyRate($currencyRate);

   /**
    * Get Status.
    * @return string
    */
    public function getStatus();

   /**
    * set Status.
    * @return $this
    */
    public function setStatus($status);

   /**
    * Get CreatedAt.
    * @return string
    */
    public function getCreatedAt();

   /**
    * set CreatedAt.
    * @return $this
    */
    public function setCreatedAt($createdAt);
}
