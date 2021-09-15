<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

/**
 * Amasty Segments Customers collection
 * Create for manipulating with Magento Customers and added Guests in Collection
 * and also automatic validate by current segment Conditions
 */
class AbstractCollection extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection
{
    const CUSTOMER_IS_GUEST_FIELD_NAME = 'customer_is_guest';

    const QUOTE_IS_ACTIVE_FIELD_NAME = 'is_active';

    const PRIMARY_FIELD_NAME = 'entity_id';

    const AMASTY_SEGMENTS_INDEX_TABLE_CUSTOMER_FIELD_NAME = 'customer_id';

    const AMASTY_SEGMENTS_INDEX_TABLE_QUOTE_FIELD_NAME = 'quote_id';

    /**
     * @var bool
     */
    protected $isIndexLoad = false;

    /**
     * @var bool
     */
    protected $isExport = false;

    /**
     * @var \Amasty\Segments\Model\SegmentRepository
     */
    protected $segmentRepository;

    /**
     * @var \Amasty\Segments\Model\Segment\SegmentContainer
     */
    protected $segmentContainer;

    /**
     * @var \Amasty\Segments\Model\ResourceModel\Index
     */
    protected $indexResource;

    /**
     * @var \Amasty\Segments\Model\Providers\GuestDataProvider
     */
    protected $guestDataProvider;

    /**
     * AbstractCollection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Amasty\Segments\Api\SegmentRepositoryInterface $segmentRepository
     * @param \Amasty\Segments\Model\Segment\SegmentContainer $segmentContainer
     * @param Index $indexResource
     * @param \Amasty\Segments\Model\Providers\GuestDataProvider $guestDataProvider
     * @param Snapshot $entitySnapshot
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Amasty\Segments\Api\SegmentRepositoryInterface $segmentRepository,
        \Amasty\Segments\Model\Segment\SegmentContainer $segmentContainer,
        \Amasty\Segments\Model\ResourceModel\Index $indexResource,
        \Amasty\Segments\Model\Providers\GuestDataProvider $guestDataProvider,
        Snapshot $entitySnapshot,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $connection,
            $resource
        );
        $this->segmentRepository = $segmentRepository;
        $this->segmentContainer = $segmentContainer;
        $this->indexResource = $indexResource;
        $this->guestDataProvider = $guestDataProvider;
    }

    /**
     * @param $segment
     * @return $this
     */
    public function setCurrentSegment($segment)
    {
        if (!is_object($segment)) {
            $segment = $this->segmentRepository->get($segment);
        }

        $this->segmentContainer->setCurrentSegment($segment);

        return $this;
    }

    /**
     * @return $this
     */
    public function getFiltersByConditions()
    {
        $items = $this->getItems();

        if ($this->segmentRepository->getSegmentFromRegistry()) {
            $saleRuleModel = $this->segmentRepository->getSegmentFromRegistry()->getSalesRule();

            foreach ($items as $item) {
                if (!$saleRuleModel->validate($item)) {
                    $this->removeItemByKey($item->getEntityId());
                }
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsIndexLoad()
    {
        return $this->isIndexLoad;
    }

    /**
     * @param bool $isIndexLoad
     * @return $this
     */
    public function setIsIndexLoad($isIndexLoad)
    {
        $this->isIndexLoad = $isIndexLoad;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsExport()
    {
        return $this->isExport;
    }

    /**
     * @param $isExport
     * @return $this
     */
    public function setIsExport($isExport)
    {
        $this->isExport = $isExport;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function beforeAddLoadedItem(\Magento\Framework\DataObject $item)
    {
        return $item;
    }

    /**
     * @return int|null
     */
    protected function getCountIndexes()
    {
        return $this->indexResource->getCountSegmentIndexes($this->segmentContainer->getCurrentSegmentId());
    }

    /**
     * @return int|null
     */
    protected function getCountGuests()
    {
        return $this->indexResource->getCountSegmentGuestsIndexes($this->segmentContainer->getCurrentSegmentId());
    }

    /**
     * @return $this
     * Needed override in inheritance classes
     */
    public function loadFromIndex()
    {
        return $this;
    }
}
