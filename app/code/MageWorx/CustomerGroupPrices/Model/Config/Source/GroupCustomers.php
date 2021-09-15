<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class GroupCustomers implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Fixed')],
            ['value' => 1, 'label' => __('Percent')]
        ];
    }
}
