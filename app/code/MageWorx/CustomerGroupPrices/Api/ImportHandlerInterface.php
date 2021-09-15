<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Api;

interface ImportHandlerInterface
{
    /**
     * Import customer group prices from CSV file
     *
     * @param mixed[] $file file info retrieved from $_FILES array
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function importFromCsvFile($file);
}