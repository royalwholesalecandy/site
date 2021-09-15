<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Plugin\Catalog\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\DB\Select;

/**
 * Plugin Collection
 * plugin name: Amasty_Sorting::SortingMethodsProcessor
 * type: \Magento\Catalog\Model\ResourceModel\Product\Collection
 */
class Collection
{
    /**
     * @var \Amasty\Sorting\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\Sorting\Model\MethodProvider
     */
    private $methodProvider;

    /**
     * @var \Amasty\Sorting\Model\SortingAdapterFactory
     */
    private $adapterFactory;

    /**
     * @var \Amasty\Sorting\Model\ResourceModel\Method\Image
     */
    private $imageMethod;

    /**
     * @var \Amasty\Sorting\Model\ResourceModel\Method\Instock
     */
    private $stockMethod;

    /**
     * @var \Amasty\Sorting\Model\Logger
     */
    private $logger;

    /**
     * Collection constructor.
     * @param \Amasty\Sorting\Helper\Data $helper
     * @param \Amasty\Sorting\Model\MethodProvider $methodProvider
     * @param \Amasty\Sorting\Model\ResourceModel\Method\Image $imageMethod
     * @param \Amasty\Sorting\Model\ResourceModel\Method\Instock $stockMethod
     * @param \Amasty\Sorting\Model\SortingAdapterFactory $adapterFactory
     * @param \Amasty\Sorting\Model\Logger $logger
     */
    public function __construct(
        \Amasty\Sorting\Helper\Data $helper,
        \Amasty\Sorting\Model\MethodProvider $methodProvider,
        \Amasty\Sorting\Model\ResourceModel\Method\Image $imageMethod,
        \Amasty\Sorting\Model\ResourceModel\Method\Instock $stockMethod,
        \Amasty\Sorting\Model\SortingAdapterFactory $adapterFactory,
        \Amasty\Sorting\Model\Logger $logger
    ) {
        $this->helper         = $helper;
        $this->methodProvider = $methodProvider;
        $this->adapterFactory = $adapterFactory;
        $this->imageMethod    = $imageMethod;
        $this->stockMethod    = $stockMethod;
        $this->logger         = $logger;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param string                                                  $attribute
     * @param string                                                  $dir  'ASC'|'DESC'
     *
     * @return array
     */
    public function beforeSetOrder($subject, $attribute, $dir = Select::SQL_DESC)
    {
        $this->stockMethod->apply($subject, $dir);
        $this->imageMethod->apply($subject, $dir);
        $method = $this->methodProvider->getMethodByCode($attribute);
        if ($method && !$subject->getFlag($this->getFlagName($attribute))) {
            $subject->setFlag($this->getFlagName($attribute), true);
            $method->apply($subject, $dir);
            $this->logger->logCollectionQuery($subject);
        }

        return [$attribute, $dir];
    }

    private function getFlagName($attribute)
    {
        if (is_string($attribute)) {
            return 'sorted_by_' . $attribute;
        }

        return 'amasty_sorting';
    }
}
