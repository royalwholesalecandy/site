<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Api\Data;

interface SegmentInterface
{
    /**
     * Constants defined for keys of data array
     */
    const SEGMENT_ID = 'segment_id';

    const NAME = 'name';

    const DESCRIPTION = 'description';

    const IS_ACTIVE = 'is_active';

    const CONDITIONS_SERIALIZED = 'conditions_serialized';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    /**
     * @return int
     */
    public function getSegmentId();

    /**
     * @param int $segmentId
     *
     * @return \Amasty\Segments\Api\Data\SegmentInterface
     */
    public function setSegmentId($segmentId);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return \Amasty\Segments\Api\Data\SegmentInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     *
     * @return \Amasty\Segments\Api\Data\SegmentInterface
     */
    public function setDescription($description);

    /**
     * @return int|string
     */
    public function getIsActive();

    /**
     * @param int|string $isActive
     *
     * @return \Amasty\Segments\Api\Data\SegmentInterface
     */
    public function setIsActive($isActive);

    /**
     * @return string
     */
    public function getConditionsSerialized();

    /**
     * @param string $conditionsSerialized
     *
     * @return \Amasty\Segments\Api\Data\SegmentInterface
     */
    public function setConditionsSerialized($conditionsSerialized);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return \Amasty\Segments\Api\Data\SegmentInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     *
     * @return \Amasty\Segments\Api\Data\SegmentInterface
     */
    public function setUpdatedAt($updatedAt);
}
