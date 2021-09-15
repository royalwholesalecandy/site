<?php

namespace MageWorx\CustomerPrices\Model;

use MageWorx\CustomerPrices\Api\Data\CustomerPricesInterface;
use Magento\Framework\DataObject\IdentityInterface;

class CustomerPrices extends \Magento\Framework\Model\AbstractModel implements CustomerPricesInterface,
    IdentityInterface
{
    const TYPE_CUSTOMER = 1;
    const TYPE_GROUP    = 2;

    const PRICE_TYPE_FIXED   = 1;
    const PRICE_TYPE_PERCENT = 2;
    const CACHE_TAG          = 'mageworx_customerprices';

    /**
     * @var \MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices
     */
    protected $resourceModel;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var double
     */
    protected $currencyAmount;

    /**
     * @var string
     */
    protected $websiteCurrencyCode;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CustomerPrices constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices $resourceModel
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices $resourceModel,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->resourceModel  = $resourceModel;
        $this->localeCurrency = $localeCurrency;
        $this->storeManager   = $storeManager;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices');
    }

    /**
     * @return array
     */
    public function getPriceTypeArray()
    {
        return [self::PRICE_TYPE_FIXED => __('Fixed'), self::PRICE_TYPE_PERCENT => __('Percent')];
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeType()
    {
        return $this->getData(self::ATTRIBUTE_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceType()
    {
        return $this->getData(self::PRICE_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecialPrice()
    {
        return $this->getData(self::SPECIAL_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecialPriceType()
    {
        return $this->getData(self::SPECIAL_PRICE_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscount()
    {
        return $this->getData(self::DISCOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountPriceType()
    {
        return $this->getData(self::DISCOUNT_PRICE_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeType($attributeType)
    {
        return $this->setData(self::ATTRIBUTE_TYPE, $attributeType);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriceType($priceType)
    {
        return $this->setData(self::PRICE_TYPE, $priceType);
    }

    /**
     * {@inheritdoc}
     */
    public function setSpecialPrice($specialPrice)
    {
        return $this->setData(self::SPECIAL_PRICE, $specialPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function setSpecialPriceType($specialPriceType)
    {
        return $this->setData(self::SPECIAL_PRICE_TYPE, $specialPriceType);
    }

    /**
     * {@inheritdoc}
     */
    public function setDiscount($discount)
    {
        return $this->setData(self::DISCOUNT, $discount);
    }

    /**
     * {@inheritdoc}
     */
    public function setDiscountPriceType($discountPriceType)
    {
        return $this->setData(self::DISCOUNT_PRICE_TYPE, $discountPriceType);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceValue()
    {
        return $this->getData(self::PRICE_VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriceValue($priceValue)
    {
        return $this->setData(self::PRICE_VALUE, $priceValue);
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecialPriceValue()
    {
        return $this->getData(self::SPECIAL_PRICE_VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setSpecialPriceValue($specialPriceValue)
    {
        return $this->setData(self::SPECIAL_PRICE_VALUE, $specialPriceValue);
    }

    /**
     * @return ResourceModel\CustomerPrices
     */
    public function getResourceModel()
    {
        return $this->resourceModel;
    }

    /**
     * @param $customerId
     * @param $productId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByCustomer($customerId, $productId)
    {
        return $this->getResourceModel()->loadByAttribute($customerId, $productId, self::TYPE_CUSTOMER);
    }

    /**
     * @return $this
     */
    protected function prepareCurrencyAmount()
    {
        $this->currencyAmount = (double)$this->getPriceValue();

        return $this;
    }

    /**
     * @return float|null
     */
    public function getCurrencyAmount()
    {
        if ($this->currencyAmount === null) {
            $this->prepareCurrencyAmount();
        }

        return $this->currencyAmount;
    }
}