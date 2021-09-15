<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Helper;

use Magento\Framework\App\Helper\Context;
use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\Data\ProductInterface;

class Calculate extends AbstractHelper
{
    const PRICE_TYPE_FIXED   = 1;
    const PRICE_TYPE_PERCENT = 2;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var string|int
     */
    protected $moduleVersion;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * Calculate constructor.
     *
     * @param Context $context
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        parent::__construct($context);
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory        = $readFactory;
        $this->metadataPool       = $metadataPool;
    }

    /**
     * @param $priceString
     * @return int
     */
    public function getPriceType($priceString)
    {
        $pos = strpos($priceString, '%');
        if ($pos === false) {
            return self::PRICE_TYPE_FIXED;
        }

        return self::PRICE_TYPE_PERCENT;
    }

    /**
     * @param $priceString
     * @return null|string
     */
    public function getPriceSign($priceString)
    {
        if (strpos($priceString, '+') !== false) {
            return '+';
        }

        if (strpos($priceString, '-') !== false) {
            return '-';
        }

        return null;
    }

    /**
     * Check correct collection
     *
     * @param $customerId
     * @param $collection
     * @return bool
     */
    public function isCheckedCollection($customerId, $collection)
    {
        /* check collection */
        if (!count($collection->getData())) {
            return false;
        }

        foreach ($collection as $product) {
            if (!$customerId || !$product instanceof Product) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $collection
     * @return array
     */
    public function getCustomerIds($collection)
    {
        $ids = [];
        foreach ($collection as $elem) {
            $ids[] = $elem['customer_id'];
        }

        $ids = array_unique($ids);

        return $ids;
    }

    /**
     * @param $collection
     * @return array
     */
    public function getProductIds($collection)
    {
        $ids = [];
        foreach ($collection as $elem) {
            $ids[] = $elem['product_id'];
        }

        $ids = array_unique($ids);

        return $ids;
    }

    /**
     * @param $collection
     * @return array
     */
    public function getIds($collection)
    {
        $ids = [];
        foreach ($collection->getItems() as $item) {
            $ids[] = $item->getEntityId();
        }

        return $ids;
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
     * @param string $moduleName
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
     * @param string $fromVersion
     * @param string $toVersion
     * @param string $fromOperator
     * @param string $toOperator
     * @param string $moduleName
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function checkModuleVersion(
        $fromVersion,
        $toVersion = '',
        $fromOperator = '>=',
        $toOperator = '<=',
        $moduleName     = 'Magento_CatalogSearch'
    ) {
        if (!isset($this->moduleVersion[$moduleName])) {
            $this->moduleVersion[$moduleName] = $this->getModuleVersion($moduleName);
        }

        $fromCondition = version_compare($this->moduleVersion[$moduleName], $fromVersion, $fromOperator);
        if ($toVersion === '') {
            return $fromCondition;
        }

        return $fromCondition && version_compare($this->moduleVersion[$moduleName], $toVersion, $toOperator);
    }

    /**
     * Srt to float and abs
     *
     * @param string $strPrice
     * @return float|null
     */
    public function getPositivePriceValue($strPrice)
    {
        if ($strPrice == '') {
            return null;
        }

        return abs(floatval($strPrice));
    }
}