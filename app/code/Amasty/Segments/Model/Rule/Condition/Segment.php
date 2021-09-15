<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition;

use \Amasty\Segments\Model\ResourceModel\Customer\Collection;

class Segment extends \Amasty\Segments\Model\Rule\Condition\Condition
{
    /**
     * @var \Amasty\Segments\Helper\Base
     */
    protected $baseHelper;

    /**
     * @var \Amasty\Segments\Model\ResourceModel\Segment\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $collectionCustomerFactory;

    /**
     * @var \Amasty\Segments\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $collectionSegmentCustomerFactory;

    /**
     * @var \Amasty\Segments\Model\Indexer\IndexBuilder
     */
    private $indexBuilder;

    /**
     * @var \Amasty\Segments\Model\ResourceModel\Index
     */
    protected $indexResource;

    /**
     * Segment constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Amasty\Segments\Helper\Base $baseHelper
     * @param \Amasty\Segments\Model\ResourceModel\Segment\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionCustomerFactory
     * @param \Amasty\Segments\Model\ResourceModel\Customer\CollectionFactory $collectionSegmentCustomerFactory
     * @param \Amasty\Segments\Model\Indexer\IndexBuilder $indexBuilder
     * @param \Amasty\Segments\Model\ResourceModel\Index $indexResource
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Amasty\Segments\Helper\Base $baseHelper,
        \Amasty\Segments\Model\ResourceModel\Segment\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionCustomerFactory,
        \Amasty\Segments\Model\ResourceModel\Customer\CollectionFactory $collectionSegmentCustomerFactory,
        \Amasty\Segments\Model\Indexer\IndexBuilder $indexBuilder,
        \Amasty\Segments\Model\ResourceModel\Index $indexResource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->baseHelper = $baseHelper;
        $this->collectionFactory = $collectionFactory;
        $this->collectionCustomerFactory = $collectionCustomerFactory;
        $this->collectionSegmentCustomerFactory = $collectionSegmentCustomerFactory;
        $this->indexBuilder = $indexBuilder;
        $this->indexResource = $indexResource;
    }

    /**
     * @return mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            $options = $this->collectionFactory->create()->addFieldToFilter('is_active', 1)->toOptionArray();
            $this->setData('value_select_options', $options);
        }

        return $this->getData('value_select_options');
    }

    /**
     * @return string
     */
    public function asHtml()
    {
        $value = '';

        try {
            $value = $this->getValueElementHtml();
        } catch (\Exception $e) {
            /**
             * if exception catch, than skip element
             */
        }

        return $this->getTypeElementHtml()
            . __(sprintf(__('Segments') . ' %s: %s', $this->getOperatorElementHtml(), $value))
            . $this->getRemoveLinkHtml();
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'multiselect';
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'multiselect';
    }

    /**
     * @param $type
     * @param $segments
     * @param $entityId
     * @return array
     */
    protected function validateByIndex($type, $segments, $entityId)
    {
        $result = [];

        $segmentItems = $this->indexResource->checkValidCustomerFromIndex($segments, $entityId, $type);
        if (count($segmentItems) > 0) {
            foreach ($segmentItems as $item) {
                $result[] = $item[\Amasty\Segments\Api\Data\SegmentInterface::SEGMENT_ID];
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $return = false;
        $customerEmail = null;
        $quoteId = null;
        $customerId = $model->getCustomerId();
        $arrSegments = $this->getValue();

        if ($customerId) {
            $result = $this->validateByIndex(
                Collection::AMASTY_SEGMENTS_INDEX_TABLE_CUSTOMER_FIELD_NAME,
                $arrSegments,
                $customerId
            );

            if ($result) {
                return $this->validateAttribute($result);
            }
        } else {
            if ($quoteId = $model->getQuoteId()) {

                return $this->validateAttribute($this->validateByIndex(
                    Collection::AMASTY_SEGMENTS_INDEX_TABLE_QUOTE_FIELD_NAME,
                    $arrSegments,
                    $quoteId
                ));
            }

            $customerEmail = $model->getEmail();
        }

        if ($customerEmail || $customerId) {
            $segmentCollection = $this->collectionFactory
                ->create()
                ->addFieldToFilter(\Amasty\Segments\Api\Data\SegmentInterface::SEGMENT_ID, ['in' => $arrSegments]);

            if ($segmentCollection->getSize()) {
                $collection = $this->collectionCustomerFactory->create();

                if ($customerId) {
                    $collection->addFieldToFilter('entity_id', ['eq' => $customerId]);
                } elseif ($customerEmail){
                    $collection->addFieldToFilter('email', ['eq' => $customerEmail]);
                }

                $customer = $collection->getFirstItem();
                $resultIds = [];

                if ($customer->getId()) {
                    foreach ($segmentCollection as $segment) {
                        $salesRule = $segment->getSalesRule();

                        if ($salesRule->validate($customer)) {
                            $resultIds[] = $segment->getId();
                        }
                    }
                }

                $this->indexBuilder->insertBySegmentIds(
                    $resultIds,
                    Collection::AMASTY_SEGMENTS_INDEX_TABLE_CUSTOMER_FIELD_NAME,
                    $customer->getId()
                );

                if (count($resultIds) > 0 ) {
                    $return = $this->validateAttribute($resultIds);
                }
            }
        }

        return $return;
    }
}

