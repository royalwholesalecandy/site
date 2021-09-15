<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Controller\Adminhtml;

use \MageWorx\CustomerPrices\Api\ExportHandlerInterfaceFactory;
use \MageWorx\CustomerPrices\Api\ImportHandlerInterfaceFactory;

abstract class ImportExport extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'MageWorx_CustomerPrices::import_export';

    /**
     * Menu id
     */
    const MENU_IDENTIFIER = 'MageWorx_CustomerPrices::system_import_export';

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var ExportHandlerInterfaceFactory
     */
    protected $exportHandlerFactory;

    /**
     * @var ImportHandlerInterfaceFactory
     */
    protected $importHandlerFactory;

    /**
     * ImportExport constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param ExportHandlerInterfaceFactory $exportHandlerFactory
     * @param ImportHandlerInterfaceFactory $importHandlerFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        ExportHandlerInterfaceFactory $exportHandlerFactory,
        ImportHandlerInterfaceFactory $importHandlerFactory
    ) {
        $this->fileFactory = $fileFactory;
        $this->exportHandlerFactory = $exportHandlerFactory;
        $this->importHandlerFactory = $importHandlerFactory;
        parent::__construct($context);
    }
}
