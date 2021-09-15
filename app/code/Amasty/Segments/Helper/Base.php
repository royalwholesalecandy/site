<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Base extends AbstractHelper
{
    /**
     * Defining all module constants
     **/
    const CONFIG_PATH_GENERAL_CRON_TIME = 'segments/general/frequency';
    const CONFIG_PATH_GENERAL_CUSTOMER_ATTRIBUTES = 'segments/general/customer_attributes';
    const CONFIG_PATH_GENERAL_ORDER_ATTRIBUTES = 'segments/general/order_attributes';
    const CURRENT_SEGMENT_REGISTRY_NAMESPACE = 'current_amasty_segments_segment';
    const CURRENT_SEGMENT_CUSTOMER_COLLECTION_REGISTRY = 'current_amasty_segments_segment_customer_collection';

    const AMASTY_SEGMENTS_WEBSITE_TABLE_NAME = 'amasty_segments_website';
    const AMASTY_SEGMENTS_EVENT_TABLE_NAME = 'amasty_segments_event';
    const AMASTY_SEGMENTS_SEGMENT_TABLE_NAME = 'amasty_segments_segment';
    const AMASTY_SEGMENTS_INDEX_TABLE_NAME = 'amasty_segments_index';

    const ARRAY_FILTER_LIKE = 'like';
    const ARRAY_FILTER_EQUALS = 'eq';
    const ARRAY_FILTER_LESS_THAN_OR_EQUALS = 'lteq';
    const ARRAY_FILTER_GRATER_THAN_OR_EQUALS = 'gteq';
    const ARRAY_FILTER_IN = 'in';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|null
     */
    protected $_scopeConfig = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * Base constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
        $this->coreRegistry = $coreRegistry;
        $this->date = $date;
    }

    /**
     * @param $path
     * @param null $storeId
     * @param string $scope
     * @return mixed
     */
    public function getConfigValueByPath(
        $path,
        $storeId = null,
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        return $this->_scopeConfig->getValue($path, $scope, $storeId);
    }

    /**
     * @param int $days
     * @param string $format
     * @return string
     */
    public function getDateDiffFormat($days, $format = 'Y-m-d')
    {
        $newDiffDate = $this->date->gmtTimestamp() - $days * 60 * 60 * 24;

        return $this->date->gmtDate($format, $newDiffDate);
    }

    /**
     * @param int    $days
     * @param string $format
     *
     * @return int
     */
    public function getTimestampDiffFormat($days, $format = 'Y-m-d')
    {
        $newDiffDate = $this->date->gmtTimestamp($this->date->gmtDate($format)) - $days * 60 * 60 * 24;

        return $newDiffDate;
    }

    /**
     * @param string $date
     * @param string $format
     *
     * @return int
     */
    public function convertToTimestamp($date, $format = 'Y-m-d')
    {
        return $this->date->gmtTimestamp($this->getFormatDate($date, $format));
    }

    /**
     * @param string $date
     * @param string $format
     * @return string
     */
    public function getFormatDate($date, $format = 'Y-m-d')
    {
        return $this->date->gmtDate($format, $date);
    }

    /**
     * @param $segment
     * @return mixed
     */
    public function initCurrentSegment($segment)
    {
        $this->coreRegistry->register(
            self::CURRENT_SEGMENT_REGISTRY_NAMESPACE,
            $segment
        );

        return $segment;
    }

    /**
     * @param $segmentConditions
     * @param $classPath
     * @return bool
     */
    public function checkExistConditionInSegment($segmentConditions, $classPath)
    {
        $res = false;

        foreach ($segmentConditions as $condition) {
            if (strpos($condition->getType(), $classPath) !== false) {
                $res = true;

                break;
            }
        }

        return $res;
    }
}
