<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Api;

interface ImportHandlerInterface
{
    /**
     * Import Customer Prices from CSV file
     *
     * @param mixed[] $file file info retrieved from $_FILES array
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function importFromCsvFile($file);
}