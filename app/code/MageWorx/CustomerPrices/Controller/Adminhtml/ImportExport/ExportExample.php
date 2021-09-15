<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\CustomerPrices\Controller\Adminhtml\ImportExport;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Component\ComponentRegistrar;

class ExportExample extends \MageWorx\CustomerPrices\Controller\Adminhtml\ImportExport
{
    /**
     * @var ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * ExportExample constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param ComponentRegistrar $componentRegistrar
     * @param \MageWorx\CustomerPrices\Api\ExportHandlerInterfaceFactory $exportHandlerFactory
     * @param \MageWorx\CustomerPrices\Api\ImportHandlerInterfaceFactory $importHandlerFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        ComponentRegistrar $componentRegistrar,
        \MageWorx\CustomerPrices\Api\ExportHandlerInterfaceFactory $exportHandlerFactory,
        \MageWorx\CustomerPrices\Api\ImportHandlerInterfaceFactory $importHandlerFactory
    ) {
        parent::__construct($context, $fileFactory,$exportHandlerFactory, $importHandlerFactory);
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Export example action from import/export customer prices
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
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'MageWorx_CustomerPrices');
        $file = $path .
            DIRECTORY_SEPARATOR .
            $relativeFilePath;
        $content = file_get_contents($file);

        return $this->fileFactory->create(
            'customer_prices_example.csv',
            $content,
            DirectoryList::VAR_DIR
        );
    }
}
