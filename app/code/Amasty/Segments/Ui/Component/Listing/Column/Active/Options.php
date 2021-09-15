<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Ui\Component\Listing\Column\Active;

use Magento\Framework\Data\OptionSourceInterface;
use Amasty\Segments\Model\Segment as Segment;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toArray()
    {
        return [
            Segment::STATUS_ENABLED => __("Active"),
            Segment::STATUS_DISABLED => __("Inactive"),
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Segment::STATUS_ENABLED,
                'label' => __("Active")
            ],
            [
                'value' => Segment::STATUS_DISABLED,
                'label' => __("Inactive")
            ],
        ];
    }
}
