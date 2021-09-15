<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition\Product\Subselect;

use Amasty\Segments\Helper\Condition\Data;

class Viewed extends \Amasty\Segments\Model\Rule\Condition\Product\Subselect
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Product\Index\Viewed\CollectionFactory
     */
    protected $collectionViewedFactory;

    /**
     * Subselect constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $conditionProduct
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $conditionProduct,
        \Magento\Reports\Model\ResourceModel\Product\Index\Viewed\CollectionFactory $collectionViewedFactory,
        array $data = []
    ) {
        parent::__construct($context, $conditionProduct, $data);
        $this->setType(Data::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Product\Subselect\Viewed')->setValue(null);
        $this->type = 'viewed';
        $this->collectionViewedFactory = $collectionViewedFactory;
    }

    /**
     * @param $customer
     * @return \Magento\Reports\Model\ResourceModel\Product\Index\Viewed\Collection
     */
    public function getValidationCollection($customer)
    {
        /** @var \Magento\Reports\Model\ResourceModel\Product\Index\Viewed\Collection $collection */
        $collection = $this->collectionViewedFactory->create();
        $collection->setCustomerId($customer->getId());
        $collection->addIndexFilter();

        return $collection;
    }
}
