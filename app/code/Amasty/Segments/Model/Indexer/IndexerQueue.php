<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Indexer;

class IndexerQueue extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var ResourceModel\Segment\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * IndexerQueue constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Amasty\Segments\Model\ResourceModel\Segment\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Segments\Model\ResourceModel\Segment\CollectionFactory $collectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->collectionFactory = $collectionFactory;
        $this->connection = $resourceConnection->getConnection();
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param $type
     * @return $this
     */
    public function eventUpdate($type)
    {
        /** @var \Amasty\Segments\Model\ResourceModel\Segment\CollectionFactory $collection */
        $collection = $this->getSegmentsCollection();
        $classPath = $this->getClassPathByType($type);

        if ($collection->getSize()) {
            foreach ($collection as $item) {
                $item->addNewEvent($type, $classPath);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function getSegmentsCollection()
    {
        return $this->collectionFactory->create()->addActiveFilter();
    }

    /**
     * @param $type
     * @return string
     */
    protected function getClassPathByType($type)
    {
        switch ($type) {
            case 'wishlist':
            case 'viewed':
                return \Amasty\Segments\Helper\Condition\Data::AMASTY_SEGMENTS_PATH_TO_CONDITIONS
                    . 'Product\\Subselect\\' . ucfirst($type);
            default:
                return \Amasty\Segments\Helper\Condition\Data::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . ucfirst($type);
        }
    }

    /**
     * @return $this
     */
    public function cleanAll()
    {
        $this->connection->truncateTable(\Amasty\Segments\Helper\Base::AMASTY_SEGMENTS_EVENT_TABLE_NAME);

        return $this;
    }
}
