<?php

namespace Metagento\Core\Model\Config\Source;

/**
 * Class NoticeType
 * @package Metagento\Core\Model\Config\Source
 */
class NotificationType implements \Magento\Framework\Option\ArrayInterface
{
    const TYPE_ANNOUNCEMENT = 'announcement';
    const TYPE_EXTENSION    = 'extension';
    const TYPE_PROMOTION    = 'promotion';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::TYPE_ANNOUNCEMENT => __('Announcement'),
            self::TYPE_EXTENSION    => __('Extensions'),
            self::TYPE_PROMOTION    => __('Promotions')
        ];
    }

    public function toOptionArray()
    {
        $options = [];

        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }
}
