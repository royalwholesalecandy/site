<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Api;

interface ExportHandlerInterface
{
    /**
     * Get content as a CSV string
     *
     * @return string
     */
    public function getContent();
}