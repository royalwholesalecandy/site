<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Api;

/**
 * Interface SegmentRepositoryInterface
 * @api
 */
interface SegmentRepositoryInterface
{
    /**
     * @param \Amasty\Segments\Api\Data\SegmentInterface $segment
     * @return \Amasty\Segments\Api\Data\SegmentInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Segments\Api\Data\SegmentInterface $segment);

    /**
     * @param int $segmentId
     * @return \Amasty\Segments\Api\Data\SegmentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($segmentId);

    /**
     * @param \Amasty\Segments\Api\Data\SegmentInterface $segment
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Segments\Api\Data\SegmentInterface $segment);

    /**
     * @param int $segmentId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($segmentId);
}
