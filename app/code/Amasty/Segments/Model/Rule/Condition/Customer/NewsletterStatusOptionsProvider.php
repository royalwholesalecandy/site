<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition\Customer;

class NewsletterStatusOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED, 'label' => __('Subscribed')],
            ['value' => \Magento\Newsletter\Model\Subscriber::STATUS_UNSUBSCRIBED, 'label' => __('Unsubscribed')],
            ['value' => \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE, 'label' => __('Not Active')],
            ['value' => \Magento\Newsletter\Model\Subscriber::STATUS_UNCONFIRMED, 'label' => __('Unconfirmed')]
        ];
    }
}
