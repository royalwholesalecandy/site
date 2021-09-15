<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Observer\Admin;

use Magento\Framework\Event\ObserverInterface;

class AddNewConditionHandle implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $additional = $observer->getAdditional();
        $conditions = $additional->getConditions();

        if (!is_array($conditions)) {
            $conditions = [];
        }

        $conditions[] = [
            'label' => __('Customers Segmentation'),
            'value' => [
                [
                    'value' => 'Amasty\Segments\Model\Rule\Condition\Segment',
                    'label' => __('Segments'),
                ]
            ]
        ];
        $additional->setConditions($conditions);
    }
}
