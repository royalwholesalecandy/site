<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Indexer;

use Amasty\Segments\Api\Data\SegmentInterface;
use Amasty\Segments\Helper\Base;
use Amasty\Segments\Model\ResourceModel\Segment\CollectionFactory as SegmentCollectionFactory;
use Amasty\Segments\Model\ResourceModel\Customer\Collection as CustomerCollection;

class IndexBuilder
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var SegmentCollectionFactory
     */
    protected $segmentCollectionFactory;

    /**
     * @var IndexerQueue
     */
    protected $indexerQueue;

    /**
     * @var \Amasty\Segments\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Amasty\Segments\Model\ResourceModel\Index
     */
    protected $indexResource;

    /**
     * @var \Amasty\Segments\Model\ResourceModel\AbstractCollection
     */
    protected $collectionFactory;

    /**
     * IndexBuilder constructor.
     * @param SegmentCollectionFactory $segmentCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Psr\Log\LoggerInterface $logger
     * @param IndexerQueue $indexerQueue
     * @param \Amasty\Segments\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Amasty\Segments\Model\ResourceModel\Index $indexResource
     * @param \Amasty\Segments\Model\ResourceModel\Customer\CollectionFactory $collectionFactory
     */
    public function __construct(
        SegmentCollectionFactory $segmentCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Segments\Model\Indexer\IndexerQueue $indexerQueue,
        \Amasty\Segments\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Amasty\Segments\Model\ResourceModel\Index $indexResource,
        \Amasty\Segments\Model\ResourceModel\Customer\CollectionFactory $collectionFactory
    ) {
        $this->resource              = $resource;
        $this->connection            = $resource->getConnection();
        $this->logger                = $logger;
        $this->segmentCollectionFactory = $segmentCollectionFactory;
        $this->indexerQueue = $indexerQueue;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->indexResource = $indexResource;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function reindexByQueue()
    {
        try {
            $this->doReindexByQueue();
        } catch (\Exception $e) {
            $this->critical($e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @return $this
     */
    protected function doReindexByQueue()
    {
        $segmentIdx = $this->getSegmentIdsForIndexByEvents();

        if (count($segmentIdx) > 0) {
            $this->reindexByIds($segmentIdx);
            $this->cleanEventTable();
        }

        return $this;
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @api
     */
    public function reindexByIds(array $ids)
    {
        try {
            $this->doReindexByIds($ids);
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Amasty segments indexing failed. See details in exception log.")
            );
        }
    }

    /**
     * @param $id
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function reindexById($id)
    {
        try {
            $this->doReindexByIds($id);
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Amasty segments indexing failed. See details in exception log.")
            );
        }
    }

    /**
     * @param $ids
     * @return $this
     */
    protected function doReindexByIds($ids)
    {
        /** @var \Amasty\Segments\Model\ResourceModel\Segment\Collection $segmentCollection */
        $segmentCollection = $this->getSegmentCollection()
            ->addFieldToFilter(SegmentInterface::SEGMENT_ID, ['in' => $ids]);

        if ($segmentCollection->getSize()) {
            $this->indexResource->cleanBySegmentIds($ids);
            $this->segmentIndexByCollection($segmentCollection);
        }

        return $this;
    }

    /**
     * Full reindex
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @api
     */
    public function reindexFull()
    {
        try {
            $this->doReindexFull();
        } catch (\Exception $e) {
            $this->critical($e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @param $segmentCollection
     */
    protected function segmentIndexByCollection($segmentCollection)
    {
        $insertData = [];

        foreach ($segmentCollection->getItems() as $item) {

            /** @var \Amasty\Segments\Model\ResourceModel\Customer\Collection $customerCollection */
            $customerCollection = $this->getCustomerCollection()->setCurrentSegment($item);
            foreach ($customerCollection->getAllEntitiesForIndex() as $customer) {

                $data = [
                    'customer_id'                => null,
                    'quote_id'                   => null,
                    SegmentInterface::SEGMENT_ID => $item->getSegmentId()
                ];

                if ($customer->getCustomerIsGuest()) {
                    $data['quote_id']    = $customer->getQuoteId();
                } else {
                    $data['customer_id'] = $customer->getId();
                }

                $insertData[] = $data;
            }
        }

        $this->indexResource->insertIndexData($insertData);
    }

    /**
     * @return CustomerCollection
     */
    protected function getCustomerCollection()
    {
        return $this->customerCollectionFactory->create();
    }

    /**
     * Full reindex Template method
     *
     * @return void
     */
    protected function doReindexFull()
    {
        $this->indexResource->cleanAllIndex();

        /** @var \Amasty\Segments\Model\ResourceModel\Segment\Collection $segmentCollection */
        $segmentCollection = $this->getSegmentCollection();
        $this->segmentIndexByCollection($segmentCollection);

        return $this;
    }

    /**
     * @return $this
     */
    protected function cleanEventTable()
    {
        $this->indexerQueue->cleanAll();

        return $this;
    }

    /**
     * @return SegmentCollectionFactory
     */
    protected function getSegmentCollection()
    {
        return $this->segmentCollectionFactory->create()->addActiveFilter();
    }

    /**
     * @return array
     */
    protected function getSegmentIdsForIndexByEvents()
    {
        $query = $this->connection
            ->select()
            ->from($this->resource->getTableName(
                Base::AMASTY_SEGMENTS_EVENT_TABLE_NAME),
                SegmentInterface::SEGMENT_ID)
            ->distinct();

        return $this->connection->fetchAll($query);
    }

    /**
     * @param \Exception $e
     * @return void
     */
    protected function critical($e)
    {
        $this->logger->critical($e);
    }

    /**
     * @param $segmentIds
     * @param $field
     * @param $fieldId
     * @return $this
     */
    public function insertBySegmentIds($segmentIds, $field, $fieldId)
    {
        $insertData = [];

        foreach ($segmentIds as $segmentId) {
            $insertData[] = array_merge([
                SegmentInterface::SEGMENT_ID => $segmentId,
            ], $this->getAddedFieldsByField($field, $fieldId));
        }

        $this->indexResource->insertIndexData($insertData);

        return $this;
    }

    /**
     * @param $field
     * @param $fieldValue
     * @return mixed
     */
    private function getAddedFieldsByField($field, $fieldValue)
    {
        $res = [
            CustomerCollection::AMASTY_SEGMENTS_INDEX_TABLE_CUSTOMER_FIELD_NAME => null,
            CustomerCollection::AMASTY_SEGMENTS_INDEX_TABLE_QUOTE_FIELD_NAME => null
        ];

        switch ($field) {
            case CustomerCollection::AMASTY_SEGMENTS_INDEX_TABLE_CUSTOMER_FIELD_NAME:
                $res[CustomerCollection::AMASTY_SEGMENTS_INDEX_TABLE_CUSTOMER_FIELD_NAME] = $fieldValue;
                break;
            case CustomerCollection::AMASTY_SEGMENTS_INDEX_TABLE_QUOTE_FIELD_NAME:
                $res[CustomerCollection::AMASTY_SEGMENTS_INDEX_TABLE_QUOTE_FIELD_NAME] = $fieldValue;
                break;
        }

        return $res;
    }
}
