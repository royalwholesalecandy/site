<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Controller\Adminhtml\ImportExport;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\CustomerGroupPrices\Model\ImportExport\ImportM1GroupPricesHandler;
use MageWorx\CustomerGroupPrices\Model\ImportExport\ImportM1GroupSpecialPricesHandler;
use \MageWorx\CustomerGroupPrices\Api\ExportHandlerInterface;
use \MageWorx\CustomerGroupPrices\Api\ImportHandlerInterface;

/**
 * Class ExpressImportPost
 */
class ExpressImportPost extends \MageWorx\CustomerGroupPrices\Controller\Adminhtml\ImportExport
{
    /**
     * @var ImportM1GroupPricesHandler
     */
    private $importM1GroupPricesHandler;

    /**
     * @var ImportM1GroupSpecialPricesHandler
     */
    private $importM1GroupSpecialPricesHandler;

    /**
     * ExpressImportPost constructor.
     *
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param ExportHandlerInterface $exportHandler
     * @param ImportHandlerInterface $importHandler
     * @param ImportM1GroupPricesHandler $importM1GroupPricesHandler
     * @param ImportM1GroupSpecialPricesHandler $importM1GroupSpecialPricesHandler
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        ExportHandlerInterface $exportHandler,
        ImportHandlerInterface $importHandler,
        ImportM1GroupPricesHandler $importM1GroupPricesHandler,
        ImportM1GroupSpecialPricesHandler $importM1GroupSpecialPricesHandler
    ) {
        parent::__construct($context, $fileFactory, $exportHandler, $importHandler);
        $this->importM1GroupPricesHandler        = $importM1GroupPricesHandler;
        $this->importM1GroupSpecialPricesHandler = $importM1GroupSpecialPricesHandler;
    }

    /**
     * Import action from import/export customer group prices
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                if (!is_null($this->getRequest()->getFiles('import_customergroupprices_file'))) {
                    $this->importHandler->importFromCsvFile(
                        $this->getRequest()->getFiles('import_customergroupprices_file')
                    );
                }

                if (!is_null($this->getRequest()->getFiles('import_migration_group_prices_file'))) {
                    $this->importM1GroupPricesHandler->importFromCsvFile(
                        $this->getRequest()->getFiles('import_migration_group_prices_file')
                    );
                }

                if (!is_null($this->getRequest()->getFiles('import_migration_special_group_prices_file'))) {
                    $this->importM1GroupSpecialPricesHandler->importFromCsvFile(
                        $this->getRequest()->getFiles('import_migration_special_group_prices_file')
                    );
                }

                $this->messageManager->addSuccessMessage(__('Data has been imported.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Invalid file upload attempt'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Invalid file upload attempt'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());

        return $resultRedirect;
    }
}
