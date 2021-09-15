<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Model;

use Webkul\AmazonMagentoConnect\Api\Data\OrderMapInterface;
use Magento\Framework\DataObject\IdentityInterface;

class OrderMap extends \Magento\Framework\Model\AbstractModel implements OrderMapInterface //, IdentityInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_amazon_maped_order';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_amazon_maped_order';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_amazon_maped_order';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\AmazonMagentoConnect\Model\ResourceModel\OrderMap');
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
     * Get AmazonOrderId.
     *
     * @return varchar
     */
    public function getAmazonOrderId()
    {
        return $this->getData(self::AMAZON_ORDER_ID);
    }

    /**
     * Set AmazonOrderId.
     */
    public function setAmazonOrderId($amazonOrderId)
    {
        return $this->setData(self::AMAZON_ORDER_ID, $amazonOrderId);
    }

    /**
     * Get MageOrderId.
     *
     * @return varchar
     */
    public function getMageOrderId()
    {
        return $this->getData(self::MAGE_ORDER_ID);
    }

    /**
     * Set MageOrderId.
     */
    public function setMageOrderId($mageOrderId)
    {
        return $this->setData(self::MAGE_ORDER_ID, $mageOrderId);
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

    /**
     * Get MageAmzAccountId.
     * @return string
     */
    public function getMageAmzAccountId()
    {
        return $this->getData(self::MAGE_AMZ_ACCOUNT_ID);
    }

    /**
     * set amzAccountId.
     * @return $this
     */
    public function setMageAmzAccountId($amzAccountId)
    {
        return $this->setData(self::MAGE_AMZ_ACCOUNT_ID, $amzAccountId);
    }

    /**
     * Get PurchaseDate.
     * @return string
     */
    public function getPurchaseDate()
    {
        return $this->getData(self::PURCHASE_DATE);
    }

    /**
     * set PurchaseDate.
     * @return $this
     */
    public function setPurchaseDate($purchaseDate)
    {
        return $this->setData(self::PURCHASE_DATE, $purchaseDate);
    }
}
