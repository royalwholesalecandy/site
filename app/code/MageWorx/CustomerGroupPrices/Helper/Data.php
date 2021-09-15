<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\ProductMetadataInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class Data extends AbstractHelper
{
    const KEY_CUSTOMER_GROUP      = 'cust_group';
    const KEY_ALL_GROUPS          = 'is_all_groups';
    const KEY_WEBSITE_ID          = 'website_id';
    const KEY_GROUP_PRICE         = 'group_price';
    const KEY_GROUP_TYPE_PRICE    = 'group_type_price';
    const KEY_ABSOLUTE_PRICE_TYPE = 'absolute_price_type';

    const GROUP_PRICE         = 0;
    const SPECIAL_GROUP_PRICE = 1;

    const PRICE_TYPE_FIXED   = 0;
    const PRICE_TYPE_PERCENT = 1;

    const ALL_WEBSITE = 0;

    /**
     * Config paths to settings
     */
    const ENABLE_CUSTOMER_GROUP_PRICE              = 'mageworx_customergroupprices/main/enabled_customer_group_price';
    const ENABLE_GROUP_PRICE_IN_CATALOG_PRICE_RULE =
        'mageworx_customergroupprices/main/enabled_group_price_in_catalog_price_rule';

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * Data constructor
     *
     * @param Context $context
     * @param ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     *
     */
    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetadata,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->productMetadata    = $productMetadata;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory        = $readFactory;
        $this->metadataPool       = $metadataPool;
        parent::__construct($context);
    }

    /**
     *
     * @param null|int $storeId
     *
     * @return bool
     */
    public function isEnabledCustomerGroupPrice($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::ENABLE_CUSTOMER_GROUP_PRICE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     *
     * @param null|int $storeId
     *
     * @return bool
     */
    public function isEnabledGroupPriceInCatalogPriceRule($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::ENABLE_GROUP_PRICE_IN_CATALOG_PRICE_RULE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param string $class
     * @return string
     * @throws \Exception
     */
    public function getLinkField($class = ProductInterface::class)
    {
        return $this->metadataPool->getMetadata($class)->getLinkField();
    }

    /**
     * @param $moduleName
     *
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getModuleVersion($moduleName)
    {
        $path             = $this->componentRegistrar->getPath(
            \Magento\Framework\Component\ComponentRegistrar::MODULE,
            $moduleName
        );
        $directoryRead    = $this->readFactory->create($path);
        $composerJsonData = $directoryRead->readFile('composer.json');
        $data             = json_decode($composerJsonData);

        return !empty($data->version) ? $data->version : 0;
    }

    /**
     * @param $groupPrice
     *
     * @return null|string
     */
    public function getMathSign($groupPrice)
    {
        if (strripos($groupPrice, '+') !== false) {
            return '+';
        }

        if (strripos($groupPrice, '-') !== false) {
            return '-';
        }

        return null;
    }

    /**
     * @return array
     */
    public function getAllowedFormatFile()
    {
        return ['text/csv', 'application/vnd.ms-excel'];
    }
}