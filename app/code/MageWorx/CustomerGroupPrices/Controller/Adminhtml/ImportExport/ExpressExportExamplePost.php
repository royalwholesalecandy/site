<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Controller\Adminhtml\ImportExport;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Component\ComponentRegistrar;
use \MageWorx\CustomerGroupPrices\Api\ExportHandlerInterface;
use \MageWorx\CustomerGroupPrices\Api\ImportHandlerInterface;

class ExpressExportExamplePost extends \MageWorx\CustomerGroupPrices\Controller\Adminhtml\ImportExport
{
    /**
     * @var ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * ExpressExportExamplePost constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param ExportHandlerInterface $exportHandler
     * @param ImportHandlerInterface $importHandler
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        ExportHandlerInterface $exportHandler,
        ImportHandlerInterface $importHandler,
        ComponentRegistrar $componentRegistrar
    ) {
        parent::__construct($context, $fileFactory, $exportHandler, $importHandler);
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Export example action from import/export customer group prices
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $relativeFilePath = implode(
            DIRECTORY_SEPARATOR,
            [
                'examples',
                'example_export.csv'
            ]
        );
        $path             = $this->componentRegistrar->getPath(
            ComponentRegistrar::MODULE,
            'MageWorx_CustomerGroupPrices'
        );
        $file             = $path .
            DIRECTORY_SEPARATOR .
            $relativeFilePath;
        $content          = file_get_contents($file);

        return $this->fileFactory->create(
            'customer_group_prices_example.csv',
            $content,
            DirectoryList::VAR_DIR
        );
    }
}
