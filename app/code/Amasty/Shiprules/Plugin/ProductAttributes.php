<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Shiprules\Plugin;

class ProductAttributes extends \Amasty\CommonRules\Plugin\ProductAttributes
{
    /**
     * ProductAttributes constructor.
     * @param \Amasty\Shiprules\Model\ResourceModel\Rule $resourceTable
     */
    public function __construct(\Amasty\Shiprules\Model\ResourceModel\Rule $resourceTable)
    {
        parent::__construct($resourceTable);
    }
}
