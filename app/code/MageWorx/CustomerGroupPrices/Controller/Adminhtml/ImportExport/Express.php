<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Controller\Adminhtml\ImportExport;

use Magento\Framework\Controller\ResultFactory;

class Express extends \MageWorx\CustomerGroupPrices\Controller\Adminhtml\ImportExport
{
    /**
     * Import and export Page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu(static::MENU_IDENTIFIER);

        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(
                \MageWorx\CustomerGroupPrices\Block\Adminhtml\ImportExport\ImportExportHeader::class
            )
        );
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(
                \MageWorx\CustomerGroupPrices\Block\Adminhtml\ImportExport\ImportExport::class
            )->setTemplate('MageWorx_CustomerGroupPrices::datatransfer/import_export.phtml')
        );

        $resultPage->getConfig()->getTitle()->prepend(__('Customer Group Prices'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import and Export Customer Group Prices'));

        return $resultPage;
    }
}
