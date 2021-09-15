<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */


namespace Amasty\Shiprules\Model\Rule\Condition;

use Amasty\CommonRules\Model\Rule\Condition\ConditionBuilder;

class CombineFactory extends \Magento\SalesRule\Model\Rule\Condition\CombineFactory
{
    /**
     * CombineFactory constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = ConditionBuilder::AMASTY_SHIP_RULES_PATH_TO_CONDITIONS . 'Combine'
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }
}
