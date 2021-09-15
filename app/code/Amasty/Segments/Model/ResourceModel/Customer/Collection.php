<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\ResourceModel\Customer;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

class Collection extends \Amasty\Segments\Model\ResourceModel\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Customer\Model\Customer::class, \Amasty\Segments\Model\ResourceModel\Customer::class);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getAllEntitiesForIndex()
    {
        $items = [];

        try {
            /**
             * Prepare select query
             * @var string $query
             */
            $query = $this->getSelect();
            $rows = $this->_fetchAll($query);

            $rows = array_merge(
                $rows,
                $this->guestDataProvider->getGuestCustomers($this->segmentContainer->getCurrentSegment())
            );
        } catch (\Exception $e) {
            $this->printLogQuery(true, true, $query);
            throw $e;
        }

        $salesRules = $this->segmentContainer->getCurrentSegmentSalesRule();
        foreach ($rows as $value) {
            $object = is_object($value) ? $value : $this->getNewEmptyItem()->setData($value);

            /** @var \Amasty\Segments\Model\SalesRule $salesRules */
            if ($salesRules && $salesRules->validate($object)) {
                array_push($items, $object);
            }
        }

        return array_values($items);
    }

    /**
     * @return $this
     */
    public function getCommonFilters()
    {
        $this->addFilterToMap('entity_id', 'main_table.entity_id')
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('email')
            ->addFieldToSelect('created_at')
            ->addFieldToSelect('firstname')
            ->addFieldToSelect('lastname')
            ->addFieldToSelect('group_id')
            ->addFieldToSelect('default_billing')
            ->addFieldToSelect('created_at')
            ->getSelect()
            ->joinLeft(
                ['address' => $this->getTable('customer_address_entity')],
                'main_table.default_billing = address.entity_id',
                ['address.telephone', 'address.country_id', 'address.region']
            );

        return $this;
    }

    /**
     * @return $this
     */
    public function loadFromIndex()
    {
        $customerIds = $this->indexResource->getIdsFromIndex(
            self::AMASTY_SEGMENTS_INDEX_TABLE_CUSTOMER_FIELD_NAME,
            $this->segmentContainer->getCurrentSegmentId()
        );

        if (!$this->getIsExport()) {
            $this->addFieldToFilter(
                self::PRIMARY_FIELD_NAME,
                ['in' => $customerIds]
            );
        }

        return $this;
    }
}
