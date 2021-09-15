<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Segment;

use Amasty\Segments\Model\Segment;

class SegmentContainer
{
    /**
     * @var \Amasty\Segments\Model\Segment
     */
    protected $currentSegment;

    /**
     * @var \Amasty\Segments\Model\SegmentRepository
     */
    protected $segmentRepository;

    /**
     * SegmentContainer constructor.
     * @param \Amasty\Segments\Api\SegmentRepositoryInterface $segmentRepository
     */
    public function __construct(
        \Amasty\Segments\Api\SegmentRepositoryInterface $segmentRepository
    ) {
        $this->segmentRepository = $segmentRepository;
        $this->init();
    }

    /**
     * @return $this
     */
    public function init()
    {
        $this->setCurrentSegment($this->segmentRepository->getSegmentFromRegistry()
            ?: $this->segmentRepository->get(null));

        return $this;
    }

    /**
     * @return Segment
     */
    public function getCurrentSegment()
    {
        return $this->currentSegment;
    }

    /**
     * @param $currentSegment
     * @return $this
     */
    public function setCurrentSegment($currentSegment)
    {
        $this->currentSegment = $currentSegment;

        return $this;
    }

    /**
     * @return int|mixed|null
     */
    public function getCurrentSegmentId()
    {
        return $this->getCurrentSegment() ? $this->getCurrentSegment()->getSegmentId() : null;
    }

    /**
     * @return int|mixed|null
     */
    public function getCurrentSegmentSalesRule()
    {
        return $this->getCurrentSegment() ? $this->getCurrentSegment()->getSalesRule() : null;
    }
}
