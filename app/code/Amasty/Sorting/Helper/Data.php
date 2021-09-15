<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Helper;

use Magento\CatalogInventory\Model\Configuration;
use Amasty\Base\Model\Serializer;

/**
 * Class Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_SORT_ORDER = 'general/sort_order';

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->serializer = $serializer;
    }

    /**
     * Get config value for Store
     *
     * @param string  $path
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     *
     * @return mixed
     */
    public function getScopeValue($path, $store = null)
    {
        return $this->scopeConfig->getValue(
            'amsorting/' . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Is Sorting Method Disabled
     *
     * @param string $methodCode
     *
     * @return bool
     */
    public function isMethodDisabled($methodCode)
    {
        $disabledMethods = $this->getScopeValue('general/disable_methods');
        if (!$disabledMethods || empty($disabledMethods)) {
            return false;
        }
        $disabledMethods = explode(',', $disabledMethods);
        foreach ($disabledMethods as $disabledCode) {
            if (trim($disabledCode) == $methodCode) {
                return true;
            }
        }

        return false;
    }

    /**
     * Getting default sorting on search pages
     *
     * @return string
     */
    public function getSearchSorting()
    {
        return $this->getScopeValue('general/default_search');
    }

    /**
     * @return bool
     */
    public function isYotpoEnabled()
    {
        return (bool)$this->getScopeValue('rating_summary/yotpo');
    }

    /**
     * @return int
     */
    public function getQtyOutStock()
    {
        return (int)$this->scopeConfig->getValue(Configuration::XML_PATH_MIN_QTY);
    }

    /**
     * @return array
     */
    public function getSortOrder()
    {
        $value = $this->getScopeValue(self::CONFIG_SORT_ORDER);
        if ($value) {
            $value = $this->serializer->unserialize($value);
        }
        if (!$value) {
            $value = [];
        }

        return $value;
    }
}
