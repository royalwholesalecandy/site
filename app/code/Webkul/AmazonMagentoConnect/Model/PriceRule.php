<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Model;

use Webkul\AmazonMagentoConnect\Api\Data\PriceRuleInterface;
use Magento\Framework\DataObject\IdentityInterface;

class PriceRule extends \Magento\Framework\Model\AbstractModel implements PriceRuleInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_amazon_pricerule';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_amazon_pricerule';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_amazon_pricerule';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\AmazonMagentoConnect\Model\ResourceModel\PriceRule');
    }
    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set EntityId.
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get price.
     *
     * @return varchar
     */
    public function getPriceFrom()
    {
        return $this->getData(self::PRICE_FROM);
    }

    /**
     * Set price.
     */
    public function setPriceFrom($price)
    {
        return $this->setData(self::PRICE_FROM, $price);
    }

    /**
     * Get price.
     *
     * @return varchar
     */
    public function getPriceTo()
    {
        return $this->getData(self::PRICE_TO);
    }

    /**
     * Set price.
     */
    public function setPriceTo($price)
    {
        return $this->setData(self::PRICE_TO, $price);
    }


    /**
     * Get price.
     *
     * @return varchar
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * Set price.
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * Get operation.
     *
     * @return varchar
     */
    public function getOperation()
    {
        return $this->getData(self::OPERATION);
    }

    /**
     * Set operation.
     */
    public function setOperation($operation)
    {
        return $this->setData(self::OPERATION, $operation);
    }

    /**
     * Get operationType.
     *
     * @return varchar
     */
    public function getOperationType()
    {
        return $this->getData(self::OPERATION_TYPE);
    }

    /**
     * Set operationType.
     */
    public function setOperationType($operationType)
    {
        return $this->setData(self::OPERATION_TYPE, $operationType);
    }

   /**
    * Get amzAccountId.
    *
    * @return varchar
    */
    public function getAmzAccountId()
    {
        return $this->getData(self::AMAZON_ACCOUNT_ID);
    }

    /**
     * Set amzAccountId.
     */
    public function setAmzAccountId($amzAccountId)
    {
        return $this->setData(self::AMAZON_ACCOUNT_ID, $amzAccountId);
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
