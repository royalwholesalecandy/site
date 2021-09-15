<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */


namespace Amasty\Shiprules\Model\ResourceModel\Rule;

class Collection extends \Amasty\CommonRules\Model\ResourceModel\Rule\Collection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Shiprules\Model\Rule', 'Amasty\Shiprules\Model\ResourceModel\Rule');
    }
}
