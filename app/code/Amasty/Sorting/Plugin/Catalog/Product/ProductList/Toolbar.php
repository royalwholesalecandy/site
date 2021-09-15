<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Plugin\Catalog\Product\ProductList;

/**
 * Plugin Toolbar
 * plugin name: Amasty_Sorting::catalogToolbar
 * type: Magento\Catalog\Block\Product\ProductList\Toolbar
 */
class Toolbar
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
     * @var \Magento\Catalog\Model\Product\ProductList\Toolbar
     */
    private $toolbarModel;

    /**
     * @var \Amasty\Sorting\Model\ResourceModel\Method\Image
     */
    private $imageMethod;

    /**
     * @var \Amasty\Sorting\Model\ResourceModel\Method\Instock
     */
    private $stockMethod;

    /**
     * Toolbar constructor.
     *
     * @param \Amasty\Sorting\Helper\Data                        $helper
     * @param \Amasty\Sorting\Model\MethodProvider               $methodProvider
     * @param \Magento\Catalog\Model\Product\ProductList\Toolbar $toolbarModel
     */
    public function __construct(
        \Amasty\Sorting\Helper\Data $helper,
        \Amasty\Sorting\Model\MethodProvider $methodProvider,
        \Magento\Catalog\Model\Product\ProductList\Toolbar $toolbarModel,
        \Amasty\Sorting\Model\ResourceModel\Method\Image $imageMethod,
        \Amasty\Sorting\Model\ResourceModel\Method\Instock $stockMethod
    ) {
        $this->helper = $helper;
        $this->methodProvider = $methodProvider;
        $this->toolbarModel = $toolbarModel;
        $this->imageMethod = $imageMethod;
        $this->stockMethod = $stockMethod;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
     * @param string                                             $dir
     *
     * @return string
     */
    public function afterGetCurrentDirection($subject, $dir)
    {
        if (!$this->toolbarModel->getDirection()) {
            if ($this->isNeedReverse($subject->getCurrentOrder())) {
                $dir = 'desc';
            } else {
                $dir = 'asc';
            }
            $subject->setDefaultDirection($dir);
        }

        return $dir;
    }

    /**
     * @param string $order
     *
     * @return bool
     */
    private function isNeedReverse($order)
    {
        $attributeCodes = $this->helper->getScopeValue('general/desc_attributes');
        if ($attributeCodes) {
            return in_array($order, explode(',', $attributeCodes)) || $order == 'relevance';
        }

        return false;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar      $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetCollection($subject, $collection)
    {
        if ($collection instanceof \Magento\Catalog\Model\ResourceModel\Product\Collection) {
            // no image sorting will be the first or the second (after stock). LIFO queue
            $this->imageMethod->apply($collection);
            // in stock sorting will be first, as the method always moves it's paremater first. LIFO queue
            $this->stockMethod->apply($collection);
        }

        return [$collection];
    }
}
