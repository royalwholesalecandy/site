<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Controller\Adminhtml\ImportExport;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

/**
 * Class ExpressExportPost
 */
class ExpressExportPost extends \MageWorx\CustomerGroupPrices\Controller\Adminhtml\ImportExport
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'MageWorx_CustomerGroupPrices::import_export';

    /**
     * Menu id
     */
    const MENU_IDENTIFIER = 'MageWorx_CustomerGroupPrices::system_import_export';

    /**
     * Export action from import/export customer group prices
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        /** @var \MageWorx\CustomerGroupPrices\Model\ImportExport\ExportHandler $exportHandler */
        $content = $this->exportHandler->getContent();

        return $this->fileFactory->create(
            'customergroupprices_' . date('Y-m-d') . '_' . time() . '.csv',
            $content,
            DirectoryList::VAR_DIR
        );
    }
}
