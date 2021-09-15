<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\ResourceModel;

use \Amasty\Segments\Api\Data\SegmentInterface as SegmentInterface;
use \Amasty\Segments\Helper\Base as Base;
use Magento\Framework\Exception\LocalizedException;

class Segment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const WEBSITE_FIELD_ENTITY = 'website_ids';

    const EVENT_NAME_FIELD_ENTITY = 'event_name';

    /**
     * @var \Amasty\Segments\Helper\Customer\Data
     */
    protected $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Segment constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Amasty\Segments\Helper\Customer\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Amasty\Segments\Helper\Customer\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_segments_segment', 'segment_id');
    }

    /**
     * @param \Amasty\Segments\Model\Segment $object
     * @return $this
     * @throws LocalizedException
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $objectData = $object->getData();

        try {
            if ($this->getWebsitesBySegmentId($object->getSegmentId())) {
                $this->getConnection()->delete(
                    $this->getTable(Base::AMASTY_SEGMENTS_WEBSITE_TABLE_NAME),
                    [
                        SegmentInterface::SEGMENT_ID . ' =?' => $object->getSegmentId()
                    ]
                );
            }

            if ($objectData
                && array_key_exists(self::WEBSITE_FIELD_ENTITY, $objectData)
                && is_array($objectData[self::WEBSITE_FIELD_ENTITY])
            ) {
                $data = [];

                foreach ($objectData[self::WEBSITE_FIELD_ENTITY] as $id) {
                    $data[] = [
                        'segment_id' => $object->getSegmentId(),
                        'website_id' => $id,
                    ];
                }

                $this->getConnection()->insertMultiple(
                    $this->getTable(Base::AMASTY_SEGMENTS_WEBSITE_TABLE_NAME),
                    $data
                );
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $websiteIds = $this->getWebsitesBySegmentId($object->getSegmentId());

        if ($object->getSegmentId()) {
            $object->setData(self::WEBSITE_FIELD_ENTITY, $websiteIds);
        }

        return $this;
    }

    /**
     * @param $segmentId
     * @return array
     */
    public function getWebsitesBySegmentId($segmentId)
    {
        return $this->getBySegmentId(
            $segmentId,
            \Amasty\Segments\Helper\Base::AMASTY_SEGMENTS_WEBSITE_TABLE_NAME,
            false,
            'website_id'
        );
    }

    /**
     * @param $segmentId
     * @param $table
     * @param bool $onlySelect
     * @param string $field
     * @return array|\Magento\Framework\DB\Select
     */
    public function getBySegmentId($segmentId, $table, $onlySelect = false, $field = '')
    {
        $findTable = $this->getTable($table);
        $select = $this->getConnection()->select()->from(['w' => $findTable], ($field ? ['w.' . $field] : '*'))
            ->where(SegmentInterface::SEGMENT_ID . ' =?', $segmentId);

        return $onlySelect ? $select : $this->getConnection()->fetchCol($select);
    }

    /**
     * @param $segmentId
     * @param $type
     * @return array
     */
    public function getEventsBySegmentId($type, $segmentId)
    {
        $select =  $this->getBySegmentId(
            $segmentId,
            \Amasty\Segments\Helper\Base::AMASTY_SEGMENTS_EVENT_TABLE_NAME,
            true
        );

        $select->where(self::EVENT_NAME_FIELD_ENTITY . '=?', $type);

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @param $type
     * @param $segmentId
     * @return bool
     */
    public function triggerEvent($type, $segmentId)
    {
        if ($type && $segmentId) {
            try {
                $existEvent = $this->getEventsBySegmentId($type, $segmentId);

                if (count($existEvent) == 0) {
                    $this->getConnection()->insertMultiple(
                        $this->getConnection()->getTableName(Base::AMASTY_SEGMENTS_EVENT_TABLE_NAME),
                        [
                            SegmentInterface::SEGMENT_ID => $segmentId,
                            self::EVENT_NAME_FIELD_ENTITY => $type,
                        ]
                    );
                }
            } catch (LocalizedException $e) {
                $this->logger->critical($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return;
    }
}

