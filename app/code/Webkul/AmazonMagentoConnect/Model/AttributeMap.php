<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Model;

use Webkul\AmazonMagentoConnect\Api\Data\AttributeMapInterface;
use Magento\Framework\DataObject\IdentityInterface;

class AttributeMap extends \Magento\Framework\Model\AbstractModel implements AttributeMapInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_amazon_attribute_map';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_amazon_attribute_map';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_amazon_attribute_map';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\AmazonMagentoConnect\Model\ResourceModel\AttributeMap');
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
    public function getMageAttr()
    {
        return $this->getData(self::MAGE_ATTR);
    }

    /**
     * Set price.
     */
    public function setMageAttr($mageAttr)
    {
        return $this->setData(self::MAGE_ATTR, $mageAttr);
    }

    /**
     * Get price.
     *
     * @return varchar
     */
    public function getAmzAttr()
    {
        return $this->getData(self::AMZ_ATTR);
    }

    /**
     * Set price.
     */
    public function setAmzAttr($amzAttr)
    {
        return $this->setData(self::AMZ_ATTR, $amzAttr);
    }


    /**
     * Get price.
     *
     * @return varchar
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set price.
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
