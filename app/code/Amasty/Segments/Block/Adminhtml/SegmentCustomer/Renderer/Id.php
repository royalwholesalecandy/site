<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Block\Adminhtml\SegmentCustomer\Renderer;

class Id extends \Amasty\Segments\Block\Adminhtml\SegmentCustomer\Renderer\AbstractRenderer
{
    /**
     * @var string
     */
    protected $rowName = 'entity_id';

    /**
     * @param $value
     * @return mixed
     */
    protected function getTextByFieldValue($value)
    {
        return $value ? __('Guest') : __('Not Guest');
    }
}
