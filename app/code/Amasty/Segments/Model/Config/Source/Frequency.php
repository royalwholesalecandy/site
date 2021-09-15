<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Config\Source;

class Frequency implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var string
     */
    protected static $_options;

    const CRON_HOURLY           = '0 * * * *';
    const CRON_TO_TIME_PER_DAY  = '0 */12 * * *';
    const CRON_DAILY            = '0 0 * * * *';
    const CRON_WEEKLY           = '0 0 * * 0';
    const CRON_MONTHLY          = '0 0 1 * *';
    const CRON_CUSTOM           = '0 0 1 * *';

    /**
     * @return array|string
     */
    public function toOptionArray()
    {
        if (!self::$_options) {
            self::$_options = [
                [
                    'label' => __('Hourly'),
                    'value' => self::CRON_HOURLY,
                ],
                [
                    'label' => __('Two Times Per Day'),
                    'value' => self::CRON_TO_TIME_PER_DAY,
                ],
                [
                    'label' => __('Daily'),
                    'value' => self::CRON_DAILY,
                ],
                [
                    'label' => __('Weekly'),
                    'value' => self::CRON_WEEKLY,
                ],
                [
                    'label' => __('Monthly'),
                    'value' => self::CRON_MONTHLY,
                ],
            ];
        }

        return self::$_options;
    }
}
