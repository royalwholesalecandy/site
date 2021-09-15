<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model;

use Amasty\Segments\Api\Data\SegmentInterface;

class Segment extends \Magento\Framework\Model\AbstractModel implements SegmentInterface
{
    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 0;

    /**
     * @var SalesRule
     */
    protected $_salesRule;

    /**
     * @var SalesRule
     */
    private $salesRuleFactory;

    /**
     * @var \Amasty\Segments\Helper\Base
     */
    protected $baseHelper;

    /**
     * @var ResourceModel\Segment
     */
    protected $segmentResource;

    /**
     * Segment constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param SalesRuleFactory $salesRuleFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Segments\Model\SalesRuleFactory $salesRuleFactory,
        \Amasty\Segments\Helper\Base $baseHelper,
        \Amasty\Segments\Model\ResourceModel\Segment $segmentResource,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->salesRuleFactory = $salesRuleFactory;
        $this->baseHelper = $baseHelper;
        $this->segmentResource = $segmentResource;
    }

    /**
     * _construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Segments\Model\ResourceModel\Segment');
        $this->setIdFieldName('segment_id');
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }

    /**
     * @return SalesRule
     */
    public function getSalesRule()
    {
        if (!$this->_salesRule) {
            $salesRuleFactory = $this->salesRuleFactory->create();
            $this->_salesRule = $salesRuleFactory->load($this->getId());
        }

        return $this->_salesRule;
    }

    /**
     * @param $type
     * @param $classPath
     * @return $this
     */
    public function addNewEvent($type, $classPath)
    {
        /** @var \Amasty\Segments\Model\ResourceModel\Segment $resource */
        $resource = $this->getResource();

        if ($type && $classPath) {
            $conditions = $this->getSalesRule()->getConditions()->getConditions();

            if ($conditions && $this->baseHelper->checkExistConditionInSegment($conditions, $classPath)) {
                $resource->triggerEvent($type, $this->getSegmentId());
            }
        }

        return $this;
    }

    /**
     * @return ResourceModel\Segment
     */
    public function getResource()
    {
        return $this->segmentResource;
    }

    /**
     * {@inheritdoc}
     */
    public function getSegmentId()
    {
        return $this->_getData(SegmentInterface::SEGMENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setSegmentId($segmentId)
    {
        $this->setData(SegmentInterface::SEGMENT_ID, $segmentId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->_getData(SegmentInterface::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->setData(SegmentInterface::NAME, $name);

        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->_getData(SegmentInterface::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->setData(SegmentInterface::DESCRIPTION, $description);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->_getData(SegmentInterface::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($isActive)
    {
        $this->setData(SegmentInterface::IS_ACTIVE, $isActive);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionsSerialized()
    {
        return $this->_getData(SegmentInterface::CONDITIONS_SERIALIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function setConditionsSerialized($conditionsSerialized)
    {
        $this->setData(SegmentInterface::CONDITIONS_SERIALIZED, $conditionsSerialized);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->_getData(SegmentInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(SegmentInterface::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->_getData(SegmentInterface::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(SegmentInterface::UPDATED_AT, $updatedAt);

        return $this;
    }
}
