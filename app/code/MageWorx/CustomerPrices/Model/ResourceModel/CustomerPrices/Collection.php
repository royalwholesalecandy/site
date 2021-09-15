<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices;

/**
 * CustomerPrices collection.
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Initializes collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
        $this->_init(
            'MageWorx\CustomerPrices\Model\CustomerPrices',
            'MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices'
        );
    }

    /**
     * Adds subscribers info to select and loads collection
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        $this->getSelect()->where(
            'main_table.attribute_type = (?)',
            \MageWorx\CustomerPrices\Model\CustomerPrices::TYPE_CUSTOMER
        );

        return parent::load($printQuery, $logQuery);
    }

}
