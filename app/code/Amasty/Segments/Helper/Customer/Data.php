<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Helper\Customer;

use Magento\Framework\Exception\NoSuchEntityException;

class Data extends \Amasty\Segments\Helper\Base
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customer;

    /**
     * @var \Amasty\Segments\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Amasty\Segments\Api\SegmentRepositoryInterface
     */
    protected $segmentRepository;

    /**
     * @var \Amasty\Segments\Model\ResourceModel\Guest\CollectionFactory
     */
    protected $guestCollectionFactory;

    /**
     * Data constructor.
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Amasty\Segments\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Amasty\Segments\Model\ResourceModel\Guest\CollectionFactory $guestCollectionFactory
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Amasty\Segments\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Amasty\Segments\Model\ResourceModel\Guest\CollectionFactory $guestCollectionFactory,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry
    ) {
        parent::__construct($context, $coreRegistry, $date);
        $this->customer = $customerFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerRegistry = $customerRegistry;
        $this->request = $context->getRequest();
        $this->guestCollectionFactory = $guestCollectionFactory;
    }

    /**
     * @return \Magento\Customer\Model\Attribute[]
     */
    public function getCustomerAttributes()
    {
        return $this->customer->create()->getAttributes();
    }

    /**
     * @return array
     */
    public function getCustomerAttributesForSource()
    {
        $attributes = $this->getCustomerAttributes();
        $result = [];

        foreach ($attributes as $attribute) {

            if (!$attribute->getFrontendLabel()) {
                continue;
            }

            if (in_array($attribute->getFrontendInput(), ['file', 'image'])) {
                continue;
            }

            if (in_array($attribute->getAttributeCode(), ['default_billing', 'default_shipping'])) {
                continue;
            }

            $result[] = $attribute;
        }

        return $result;
    }

    /**
     * @param $customerEmail
     * @param $websiteId
     * @return \Magento\Customer\Model\Customer|null
     */
    public function getCustomerByEmail($customerEmail, $websiteId)
    {
        try {
            return $this->customerRegistry->retrieveByEmail($customerEmail, $websiteId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @param $customerEmail
     * @param string $websiteId
     * @return bool
     */
    public function checkExistCustomerByEmail($customerEmail, $websiteId = '')
    {
        $customer = $this->getCustomerByEmail($customerEmail, $websiteId);

        return ($customer && $customer->getId()) ? true : false;
    }

    /**
     * @return \Amasty\Segments\Model\ResourceModel\Customer\Collection
     */
    public function getFilteredCustomerCollection()
    {
        return $this->getFilterCollection($this->customerCollectionFactory);
    }

    /**
     * @return \Amasty\Segments\Model\ResourceModel\Guest\Collection
     */
    public function getFilteredGuestCollection()
    {
        return $this->getFilterCollection($this->guestCollectionFactory);

    }

    /**
     * @param $collectionFactory
     * @return \Amasty\Segments\Model\ResourceModel\Guest\Collection
     * |\Amasty\Segments\Model\ResourceModel\Customer\Collection
     */
    public function getFilterCollection($collectionFactory)
    {
        $currentSegmentId = $this->request->getParam(
            \Amasty\Segments\Controller\Adminhtml\Segment::SEGMENT_PARAM_URL_KEY, 0
        );
        $collection = $collectionFactory
            ->create()
            ->setCurrentSegment($currentSegmentId)
            ->getCommonFilters()
            ->loadFromIndex();

        return $collection;
    }
}
