<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */


/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Shiprules\Model\Source;

use Amasty\Shiprules\Model\Rule;
use Amasty\CommonRules\Model\Rule as CommonRule;


class Backorders
{
    public function toArray()
    {
        return [
            CommonRule::ALL_ORDERS => __('All orders'),
            CommonRule::BACKORDERS_ONLY => __('Backorders only'),
            CommonRule::NON_BACKORDERS => __('Non backorders')
        ];
    }
}
