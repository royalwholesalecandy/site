<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\ResourceModel\Segment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Amasty\Segments\Api\Data\SegmentInterface;

/**
 * @method \Amasty\Segments\Model\Segment[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->metadataPool = $metadataPool;
        return parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * _construct
     */
    protected function _construct()
    {
        $this->_init('Amasty\Segments\Model\Segment', 'Amasty\Segments\Model\ResourceModel\Segment');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @return $this
     */
    protected function _afterLoad()
    {
        $entityMetadata = $this->metadataPool->getMetadata(SegmentInterface::class);
        $this->performAfterLoad(
            'amasty_segments_website',
            'amasty_segments_entity_website',
            $entityMetadata->getLinkField(),
            'segment_id',
            'website_id'
        );

        return parent::_afterLoad();
    }

    /**
     * @return $this
     */
    public function addActiveFilter()
    {
        $this->addFieldToFilter(\Amasty\Segments\Api\Data\SegmentInterface::IS_ACTIVE, ['eq' => 1]);

        return $this;
    }

    /**
     * @param string $tableName
     * @param string $alias
     * @param string $linkField
     * @param string $fkField
     * @param string $targetField
     */
    protected function performAfterLoad($tableName, $alias, $linkField, $fkField, $targetField)
    {
        $linkedIds = $this->getColumnValues($linkField);

        if (count($linkedIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from([$alias => $this->getTable($tableName)])
                ->where($alias . '.' . $fkField . ' IN (?)', $linkedIds);
            $result = $connection->fetchAll($select);

            if ($result) {
                $data = [];

                foreach ($result as $item) {
                    $data[$item[$fkField]][] = $item[$targetField];
                }

                foreach ($this->getItems() as $item) {
                    $linkedId = $item->getData($linkField);
                    if (!isset($data[$linkedId])) {
                        continue;
                    }
                    $item->setData($targetField, $data[$linkedId]);
                }
            }
        }
    }

    /**
     * Join store relation table if there is store filter
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function joinStoreRelationTable($tableName, $linkField)
    {
        if ($this->getFilter('website_id')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable($tableName)],
                'main_table.entity_id = store_table.' . $linkField,
                []
            )->group(
                'main_table.entity_id'
            );
        }

        parent::_renderFiltersBefore();
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('amasty_segments_website', 'segment_id');
    }
}
