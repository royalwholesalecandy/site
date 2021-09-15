<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Plugin\Catalog;

/**
 * Plugin Config
 * plugin name: AddSortingMethods
 * type: \Magento\Catalog\Model\Config
 */
class Config
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
     * @var array
     */
    private $correctSortOrder;

    public function __construct(
        \Amasty\Sorting\Helper\Data $helper,
        \Amasty\Sorting\Model\MethodProvider $methodProvider,
        \Amasty\Sorting\Model\SortingAdapterFactory $adapterFactory
    ) {
        $this->helper = $helper;
        $this->methodProvider = $methodProvider;
        $this->adapterFactory = $adapterFactory;
        $this->correctSortOrder = array_keys($this->helper->getSortOrder());
    }

    /**
     * Retrieve Attributes array used for sort by
     *
     * @param \Magento\Catalog\Model\Config $subject
     * @param array $options
     *
     * @return array
     */
    public function afterGetAttributesUsedForSortBy($subject, $options)
    {
        return $this->addNewOptions($options);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function addNewOptions($options)
    {
        $methods = $this->methodProvider->getMethods();

        foreach ($methods as $methodObject) {
            $code = $methodObject->getMethodCode();
            if (!$this->helper->isMethodDisabled($code) && !isset($options[$code])) {
                $options[$code] = $this->adapterFactory->create(['methodModel' => $methodObject]);
            }
        }

        return $options;
    }

    /**
     * Retrieve Attributes Used for Sort by as array
     * key = code, value = name
     *
     * @param \Magento\Catalog\Model\Config $subject
     * @param array $options
     *
     * @return array
     */
    public function afterGetAttributeUsedForSortByArray($subject, $options)
    {
        if ((bool)$this->helper->getScopeValue('general/hide_best_value')) {
            unset($options['position']);
        }
        $options = $this->sortOptions($options);

        return $options;
    }

    /**
     * @param array $options
     * @return array $sortedOptions
     */
    private function sortOptions($options = [])
    {
        uksort($options, array($this, "sortingRule"));

        return $options;
    }

    private function sortingRule($first, $second)
    {
        if (array_search($first, $this->correctSortOrder) < array_search($second, $this->correctSortOrder)) {
            return -1;
        } elseif (array_search($first, $this->correctSortOrder) == array_search($second, $this->correctSortOrder)) {
            return 0;
        } else {
            return 1;
        }
    }
}
