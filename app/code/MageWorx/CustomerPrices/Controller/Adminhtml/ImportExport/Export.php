<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\CustomerPrices\Controller\Adminhtml\ImportExport;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

class Export extends \MageWorx\CustomerPrices\Controller\Adminhtml\ImportExport
{
    /**
     * Export action from import/export customer prices
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        /** @var \MageWorx\CustomerPrices\Model\ImportExport\ExportHandler $exportHandler */
        $exportHandler = $this->exportHandlerFactory->create();
        $content = $exportHandler->getContent();

        return $this->fileFactory->create(
            'customer_prices' . date('Y-m-d') . '_' . time() . '.csv',
            $content,
            DirectoryList::VAR_DIR
        );
    }
}