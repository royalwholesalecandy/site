<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Model;

use MageWorx\CustomerGroupPrices\Api\Data\CustomerGroupPricesInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\FactoryInterface;
use MageWorx\CustomerGroupPrices\Helper\Data as Helper;
use Magento\Framework\Model\AbstractModel;

class CustomerGroupPrices extends AbstractModel implements CustomerGroupPricesInterface, IdentityInterface
{
    const PRICE_TYPE_FIXED       = 0;
    const PRICE_TYPE_PERCENT     = 1;
    const ABSOLUTE_PRICE         = 0;
    const ABSOLUTE_SPECIAL_PRICE = 1;
    const CACHE_TAG              = 'mageworx_customerprices';

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var FactoryInterface
     */
    protected $factoryInterface;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var Helper
     */
    protected $helperData;

    /**
     * @var ResourceModel\CustomerGroupPrices
     */
    protected $resourceModel;

    /**
     * CustomerGroupPrices constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Helper $helperData
     * @param TransportBuilder $transportBuilder
     * @param FactoryInterface $templateFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\ObjectManagerInterface $objectFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param ResourceModel\CustomerGroupPrices $resourceModel
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Helper $helperData,
        TransportBuilder $transportBuilder,
        FactoryInterface $templateFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\ObjectManagerInterface $objectFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices $resourceModel,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->transportBuilder = $transportBuilder;
        $this->factoryInterface = $templateFactory;
        $this->scopeConfig      = $scopeConfig;
        $this->objectFactory    = $objectFactory;
        $this->urlBuilder       = $urlBuilder;
        $this->assetRepo        = $assetRepo;
        $this->helperData       = $helperData;
        $this->resourceModel    = $resourceModel;
    }

    protected function _construct()
    {
        $this->_init('MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices');
    }

    /**
     * {@inheritdoc}
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
    public function getGroupId()
    {
        return $this->getData(self::GROUP_ID);
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
    public function setId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
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
    public function getResourceModel()
    {
        return $this->resourceModel;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroupId($groupId)
    {
        return $this->setData(self::GROUP_ID, $groupId);
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * Get Absolute Price Type (Use only for product: 0 - Price, 1 - Special Price)
     *
     * {@inheritdoc}
     */
    public function getAbsolutePriceType()
    {
        return $this->getData(self::ABSOLUTE_PRICE_TYPE);
    }

    /**
     * Set Absolute Price Type (0 - Price, 1 - Special Price)
     *
     * {@inheritdoc}
     */
    public function setAbsolutePriceType($absolutePriceType)
    {
        return $this->setData(self::ABSOLUTE_PRICE_TYPE, $absolutePriceType);
    }
}