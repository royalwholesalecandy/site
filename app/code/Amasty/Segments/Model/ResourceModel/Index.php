<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\ResourceModel;

use Amasty\Segments\Helper\Base;
use \Amasty\Segments\Api\Data\SegmentInterface as SegmentInterface;
use \Amasty\Segments\Model\ResourceModel\Customer\Collection;

class Index extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_segments_index', 'index_id');
    }

    /**
     * @param string $field
     * @return array
     */
    public function getIdsFromIndex($field, $segmentId)
    {
        $query = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), $field)
            ->distinct()
            ->where(SegmentInterface::SEGMENT_ID . ' = ?', $segmentId)
            ->where($field . ' IS NOT NULL');

        return $this->getConnection()->fetchAll($query);
    }

    /**
     * @param int|string $segmentIds
     * @param int|string $entityId
     * @param string $field
     * @return array
     */
    public function checkValidCustomerFromIndex($segmentIds, $entityId, $field)
    {
        $query = $this->getConnection()
            ->select()
            ->from(
                $this->getMainTable(),
                SegmentInterface::SEGMENT_ID
            )
            ->distinct()
            ->where(
                SegmentInterface::SEGMENT_ID .
                ' IN (?)', $segmentIds
            )
            ->where($field . ' = :'. $field);

        return $this->getConnection()->fetchAll($query, [$field => $entityId]);
    }

    /**
     * @param $segmentsIds
     */
    public function cleanBySegmentIds($segmentsIds)
    {
        $query = $this->getConnection()->deleteFromSelect(
            $this->getConnection()
                ->select()
                ->from($this->getMainTable(), SegmentInterface::SEGMENT_ID)
                ->where(SegmentInterface::SEGMENT_ID . ' IN (?)', $segmentsIds),
            $this->getMainTable()
        );

        $this->getConnection()->query($query);
    }

    /**
     * @param $segmentId
     * @return int|void
     */
    public function getCountSegmentIndexes($segmentId)
    {
        return $this->getCountIndex($segmentId);
    }

    /**
     * @param $segmentId
     * @return int|void
     */
    public function getCountSegmentGuestsIndexes($segmentId)
    {
        return $this->getCountIndex($segmentId, true);
    }

    /**
     * @param int|string $segmentId
     * @param bool $guest
     * @return int|void
     */
    public function getCountIndex($segmentId, $guest = false)
    {
        $query = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), SegmentInterface::SEGMENT_ID)
            ->where(SegmentInterface::SEGMENT_ID . ' IN (?)', $segmentId);

        if ($guest) {
            $query->where(Collection::AMASTY_SEGMENTS_INDEX_TABLE_QUOTE_FIELD_NAME . ' IS NOT NULL');
        }

        return count($this->getConnection()->fetchAll($query));
    }

    /**
     * @return $this
     */
    public function cleanAllIndex()
    {
        $this->getConnection()->truncateTable($this->getMainTable());

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function insertIndexData(array $data)
    {
        $this->getConnection()->insertMultiple($this->getMainTable(), $data);

        return $this;
    }
}
