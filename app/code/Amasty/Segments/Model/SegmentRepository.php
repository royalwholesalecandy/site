<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model;

use Amasty\Segments\Api\Data;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class SegmentRepository implements \Amasty\Segments\Api\SegmentRepositoryInterface
{
    /**
     * @var array
     */
    protected $segment = [];

    /**
     * @var ResourceModel\Segment
     */
    protected $segmentResource;

    /**
     * @var SegmentFactory
     */
    protected $segmentFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * SegmentRepository constructor.
     * @param ResourceModel\Segment $segmentResource
     * @param SegmentFactory $segmentFactory
     */
    public function __construct(
        \Amasty\Segments\Model\ResourceModel\Segment $segmentResource,
        \Amasty\Segments\Model\SegmentFactory $segmentFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->segmentResource = $segmentResource;
        $this->segmentFactory = $segmentFactory;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\SegmentInterface $segment)
    {
        if ($segment->getSegmentId()) {
            $segment = $this->get($segment->getSegmentId())->addData($segment->getData());
        }

        try {
            $this->segmentResource->save($segment);
            unset($this->segment[$segment->getSegmentId()]);
        } catch (\Exception $e) {
            if ($segment->getSegmentId()) {
                throw new CouldNotSaveException(
                    __('Unable to save Segment with ID %1. Error: %2', [$segment->getSegmentId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new request. Error: %1', $e->getMessage()));
        }

        return $segment;
    }

    /**
     * {@inheritdoc}
     */
    public function get($segmentId)
    {
        if (!$segmentId) {
            return $this->segmentFactory->create();
        }

        if (!isset($this->segment[$segmentId])) {

            /** @var \Amasty\Segments\Model\Segment $segment */
            $segment = $this->segmentFactory->create();
            $this->segmentResource->load($segment, $segmentId);

            if (!$segment->getSegmentId()) {
                throw new NoSuchEntityException(__('Segment with id "%1" does not exist.', $segmentId));
            }

            $this->segment[$segmentId] = $segment;
        }

        return $this->segment[$segmentId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\SegmentInterface $segment)
    {
        try {
            $this->segmentResource->delete($segment);
            unset($this->segment[$segment->getSegmentId()]);
        } catch (\Exception $e) {
            if ($segment->getSegmentId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove form with ID %1. Error: %2', [$segment->getSegmentId(), $e->getMessage()])
                );
            }

            throw new CouldNotDeleteException(__('Unable to remove form. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($segmentId)
    {
        $model = $this->get($segmentId);
        $this->delete($model);

        return true;
    }

    /**
     * @return mixed
     */
    public function getSegmentFromRegistry()
    {
        return $this->coreRegistry->registry(\Amasty\Segments\Helper\Base::CURRENT_SEGMENT_REGISTRY_NAMESPACE);
    }
}
