<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Api;

interface ExportHandlerInterface
{
    /**
     * Get content as a CSV string
     *
     * @param mixed[] $entities
     * @param mixed[] $ids
     * @return string
     */
    public function getContent($entities = [], $ids = []);
}