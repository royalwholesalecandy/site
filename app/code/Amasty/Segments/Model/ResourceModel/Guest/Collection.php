<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\ResourceModel\Guest;

use Amasty\Segments\Model\ResourceModel\AbstractCollection;

class Collection extends \Amasty\Segments\Model\ResourceModel\AbstractCollection
{
    const SELECT_ADDRESS_QUOTE_TYPE = 'billing';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Quote\Model\Quote::class, \Magento\Quote\Model\ResourceModel\Quote::class);
    }

    /**
     * @return $this
     */
    public function loadFromIndex()
    {
        $indexQuoteIds = $this->guestDataProvider->getQuoteIdsFromIndex(
            $this->segmentContainer->getCurrentSegmentId()
        );

        if (!$this->getIsExport()) {
            $this->addFieldToFilter(
                AbstractCollection::PRIMARY_FIELD_NAME,
                ['in' => $indexQuoteIds]
            );
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function getCommonFilters()
    {
        $this
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('store_id')
            ->addFieldToSelect('created_at')
            ->addFieldToSelect('customer_group_id', 'group_id')
            ->getSelect()
            ->joinLeft(
                ['address' => $this->getTable('quote_address')],
                'main_table.entity_id = address.quote_id',
                ['email', 'firstname', 'lastname', 'country_id', 'region', 'telephone']
            )
            ->where('address.address_type =?', self::SELECT_ADDRESS_QUOTE_TYPE);

        return $this;
    }
}
