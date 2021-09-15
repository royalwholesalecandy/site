<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Shiprules\Model\Grid;

class CalcOptions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Amasty\CommonRules\Model\OptionProvider\Pool
     */
    protected $poolOptionProvider;

    /**
     * @param \Amasty\CommonRules\Model\OptionProvider\Pool $poolOptionProvider
     */
    public function __construct(\Amasty\CommonRules\Model\OptionProvider\Pool $poolOptionProvider)
    {
        $this->poolOptionProvider = $poolOptionProvider;
    }

    /**
     * Return backup types array
     * @return array
     */
    public function toOptionArray()
    {
        return $this->poolOptionProvider->getOptionsByProviderCode('calculation');
    }
}