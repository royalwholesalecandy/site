<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Model;

use Webkul\AmazonMagentoConnect\Api\Data\AccountsInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Accounts extends \Magento\Framework\Model\AbstractModel implements AccountsInterface //, IdentityInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_amazon_accounts';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_amazon_accounts';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_amazon_accounts';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\AmazonMagentoConnect\Model\ResourceModel\Accounts');
    }
    /**
     * Get Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set Id.
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * Get Country.
     *
     * @return varchar
     */
    public function getCountry()
    {
        return $this->getData(self::COUNTRY);
    }

    /**
     * Set Country.
     */
    public function setCountry($country)
    {
        return $this->setData(self::COUNTRY, $country);
    }

    /**
     * Get SellerId.
     *
     * @return varchar
     */
    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }

    /**
     * Set SellerId.
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Get MarketplaceId.
     *
     * @return varchar
     */
    public function getMarketplaceId()
    {
        return $this->getData(self::MARKETPLACE_ID);
    }

    /**
     * Set marketplaceId.
     */
    public function setMarketplaceId($marketplaceId)
    {
        return $this->setData(self::MARKETPLACE_ID, $marketplaceId);
    }
    
    /**
     * Get AccessKeyId.
     *
     * @return varchar
     */
    public function getAccessKeyId()
    {
        return $this->getData(self::ACCESS_KEY_ID);
    }

    /**
     * Set AccessKeyId.
     */
    public function setAccessKeyId($accessKeyId)
    {
        return $this->setData(self::ACCESS_KEY_ID, $accessKeyId);
    }

    /**
     * Get SecretKey.
     *
     * @return varchar
     */
    public function getSecretKey()
    {
        return $this->getData(self::SECRET_KEY);
    }

    /**
     * Set SecretKey.
     */
    public function setSecretKey($secretKey)
    {
        return $this->setData(self::SECRET_KEY, $secretKey);
    }

    /**
     * Get currency code.
     *
     * @return varchar
     */
    public function getCurrencyCode()
    {
        return $this->getData(self::CURRENCY_CODE);
    }

    /**
     * Set currency code.
     */
    public function setCurrencyCode($currencyCode)
    {
        return $this->setData(self::CURRENCY_CODE, $currencyCode);
    }

    /**
     * Get currency rate.
     *
     * @return varchar
     */
    public function getCurrencyRate()
    {
        return $this->getData(self::CURRENCY_RATE);
    }

    /**
     * Set currency code.
     */
    public function setCurrencyRate($currencyRate)
    {
        return $this->setData(self::CURRENCY_RATE, $currencyRate);
    }

    /**
     * Get Status.
     *
     * @return varchar
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Status.
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
