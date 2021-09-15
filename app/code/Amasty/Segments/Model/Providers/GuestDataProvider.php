<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Providers;

use \Amasty\Segments\Model\ResourceModel\Customer\Collection as CustomerCollection;
use \Magento\Quote\Model\Quote\Address;
use \Magento\Framework\Api\SimpleDataObjectConverter;

class GuestDataProvider
{
    const GUEST_COLLECTION_TYPE_QUOTE = 'quote';

    const COLLECTION_TYPE_QUOTE_FIELD = 'address_type';

    const CUSTOMER_QUOTE_COLLECTION_PAGE_SIZE = 1000;

    const CUSTOMER_IS_GUEST_VALUE = 1;

    const QUOTE_IS_ACTIVE_VALUE = 1;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected $customerCollection;

    /**
     * @var \Amasty\Segments\Model\GuestCustomerDataFactory
     */
    protected $guestCustomerDataFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * @var \Amasty\Segments\Model\ResourceModel\Index
     */
    protected $indexResource;

    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    protected $quoteAddressFactory;

    /**
     * GuestDataProvider constructor.
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Amasty\Segments\Model\GuestCustomerDataFactory $guestCustomerDataFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
     * @param \Amasty\Segments\Model\ResourceModel\Index $indexResource
     */
    function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Amasty\Segments\Model\GuestCustomerDataFactory $guestCustomerDataFactory,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Amasty\Segments\Model\ResourceModel\Index $indexResource,
        \Magento\Quote\Model\ResourceModel\Quote\Address\CollectionFactory $quoteAddressFactory
    ) {
        $this->customerCollection = $customerCollectionFactory->create();
        $this->guestCustomerDataFactory = $guestCustomerDataFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->indexResource = $indexResource;
        $this->quoteAddressFactory = $quoteAddressFactory;
    }

    /**
     * @param $segment
     * @return array
     */
    public function getGuestCustomers($segment)
    {
        $guests = [];
        $websiteIds = $segment->getWebsiteIds();
        foreach ($this->getCollectionsForGuest() as $type => $collection) {
            if ($collection->getSize()) {
                $collection->addFieldToSelect('customer_email');
                $this->customerCollection->getSelect()->where('e.email IN (' . $collection->getSelect() .')');
                $collection->addFieldToSelect('*');

                if ($websiteIds) {
                    $this->customerCollection->addFieldToFilter('website_id', ['in' => $websiteIds]);
                }

                $registeredEmails = $this->customerCollection->addFieldToSelect('email')->getColumnValues('email');

                $collection
                    ->addFieldToFilter(
                        ['customer_email', 'customer_email'],
                        [
                            ['null' => true],
                            ['nin'  => ($registeredEmails ? $registeredEmails : 1)]
                        ]
                    )
                    ->setPageSize(self::CUSTOMER_QUOTE_COLLECTION_PAGE_SIZE);

                $collection = $this->addGuestQuoteFilters($collection);
                $pages = $collection->getLastPageNumber();

                for ($i = 1; $i <= $pages; $i++) {
                    $collection->resetData();
                    $collection->setCurPage($i);
                    $items = $collection->getData();

                    /** @var \Magento\Quote\Model\Quote $item */
                    foreach ($items as $customerItem) {
                        $guests[] = $this->prepareGuestCustomerModel($customerItem);
                    }

                    $collection->clear();
                    gc_collect_cycles();
                }
            }
        }

        return $guests;
    }

    /**
     * @param $collection
     * @return \Magento\Quote\Model\ResourceModel\Quote\Collection
     */
    protected function addGuestQuoteFilters($collection)
    {
        return $collection
            ->addFieldToSelect('entity_id', 'quote_id')
            ->addFieldToSelect('store_id')
            ->addFieldToSelect('customer_email')
            ->addFieldToSelect('created_at')
            ->addFieldToSelect('updated_at')
            ->addFieldToSelect('base_grand_total')
            ->addFieldToSelect('grand_total')
            ->addFieldToSelect('items_qty')
            ->addFieldToSelect('customer_group_id', 'group_id');
    }

    /**
     * @return \Magento\Quote\Model\ResourceModel\Quote\Collection
     */
    protected function getGuestQuoteCollection()
    {
        return $this->quoteCollectionFactory
            ->create()
            ->addFieldToFilter(
                CustomerCollection::CUSTOMER_IS_GUEST_FIELD_NAME, ['eq' => self::CUSTOMER_IS_GUEST_VALUE]
            )
            /*->addFieldToFilter(
                CustomerCollection::QUOTE_IS_ACTIVE_FIELD_NAME, ['eq' => self::QUOTE_IS_ACTIVE_VALUE]
            )*/;
    }

    /**
     * @param string $quoteId
     * @param string $type
     * @return array|null
     */
    protected function getAddressByType($quoteId, $type)
    {
        $addressData = $this->quoteAddressFactory
            ->create()
            ->addFieldToSelect('firstname')
            ->addFieldToSelect('email')
            ->addFieldToSelect('lastname')
            ->addFieldToSelect('city')
            ->addFieldToSelect('region')
            ->addFieldToSelect('region_id')
            ->addFieldToSelect('country_id')
            ->addFieldToSelect('postcode')
            ->addFieldToSelect('telephone')
            ->addFieldToFilter(CustomerCollection::AMASTY_SEGMENTS_INDEX_TABLE_QUOTE_FIELD_NAME, ['eq' => $quoteId])
            ->addFieldToFilter(self::COLLECTION_TYPE_QUOTE_FIELD, ['eq' => $type])
            ->getFirstitem();

        return $addressData;
    }

    /**
     * @param $quoteId
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuoteByIdFromCollection($quoteId)
    {
        return $this->quoteCollectionFactory
            ->create()
            ->addFieldToFilter(CustomerCollection::PRIMARY_FIELD_NAME, ['eq' => $quoteId])
            ->getFirstItem();
    }

    /**
     * @param $quoteIds
     * @return \Magento\Quote\Model\ResourceModel\Quote\Collection
     */
    protected function getQuotesCollectionByIds($quoteIds)
    {
        $collection = $this->quoteCollectionFactory->create()
            ->addFieldToFilter(CustomerCollection::PRIMARY_FIELD_NAME, ['in' => $quoteIds]);

        return $this->addGuestQuoteFilters($collection);
    }

    /**
     * @param $segmentId
     * @return array
     */
    public function getQuoteIdsFromIndex($segmentId)
    {
        return $this->indexResource->getIdsFromIndex(
            CustomerCollection::AMASTY_SEGMENTS_INDEX_TABLE_QUOTE_FIELD_NAME,
            $segmentId
        );
    }

    /**
     * @param $segment
     * @param int $limitCount
     * @param int $curPage
     * @return array
     */
    public function getGuestCustomersFromIndex($segment, $limitCount = 0, $curPage = 0)
    {
        $guests = [];
        $quoteIds = $this->getQuoteIdsFromIndex($segment->getSegmentId());
        $quoteCollection = $this->getQuotesCollectionByIds($quoteIds);

        if ($quoteCollection->getSize()) {
            if ($limitCount && $curPage) {
                $items = $quoteCollection
                    ->setPageSize($limitCount)
                    ->setCurPage($curPage)
                    ->getData();

                $guests = $this->getGuestsFromQuoteItems($items);

                $quoteCollection->clear();
                gc_collect_cycles();
            } else {
                $quoteCollection->setPageSize(self::CUSTOMER_QUOTE_COLLECTION_PAGE_SIZE);
                $pages = $quoteCollection->getLastPageNumber();

                for ($i = 1; $i <= $pages; $i++) {
                    $quoteCollection->setCurPage($i);
                    $items = $quoteCollection->getData();
                    $guests = array_merge($guests, $this->getGuestsFromQuoteItems($items));
                    $quoteCollection->clear();
                    gc_collect_cycles();
                }
            }
        }

        return $guests;
    }

    /**
     * @param $items
     * @return array
     */
    protected function getGuestsFromQuoteItems($items)
    {
        $guests = [];

        /** @var \Magento\Quote\Model\Quote $item */
        foreach($items as $item)
        {
            $guests[] = $this->prepareGuestCustomerModel($item);
        }

        return $guests;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection[]
     */
    private function getCollectionsForGuest()
    {
        return [
            self::GUEST_COLLECTION_TYPE_QUOTE => $this->getGuestQuoteCollection()
        ];
    }

    /**
     * @param $model
     * @return \Amasty\Segments\Model\GuestCustomerData
     */
    protected function prepareGuestCustomerModel($model)
    {
        /** @var \Amasty\Segments\Model\GuestCustomerData $newCustomerObject */
        $newCustomerObject = $this->guestCustomerDataFactory->create();
        $shippingAddress = $this->getAddressByType($model['quote_id'], Address::ADDRESS_TYPE_SHIPPING);
        $billingAddress = $this->getAddressByType($model['quote_id'], Address::ADDRESS_TYPE_BILLING);

        if ($shippingAddress) {
            $newCustomerObject->setData($shippingAddress->getData());
        } elseif ($billingAddress) {
            $newCustomerObject->setData($billingAddress->getData());
        } else {
            foreach ($model as $key => $value) {
                if (strpos($key, 'customer_') !== false) {
                    $customerAttributeName = substr($key, strlen('customer_'));
                    if ($customerAttributeName) {
                        $newCustomerObject->setData($customerAttributeName, $value);
                    }
                }
            }
        }

        $newCustomerObject->setData($model);
        $newCustomerObject->setDefaultBillingAddress($billingAddress);
        $newCustomerObject->setDefaultShippingAddress($shippingAddress);
        $newCustomerObject->setCustomerIsGuest(1);

        return $newCustomerObject;
    }
}
