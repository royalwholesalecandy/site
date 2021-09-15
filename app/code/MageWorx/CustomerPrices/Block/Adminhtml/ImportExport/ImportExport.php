<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Block\Adminhtml\ImportExport;

use Magento\Backend\Block\Template\Context;

/**
 * Class ImportExport
 *
 *
 * @method bool|null getIsReadonly()
 * @method ImportExport setUseContainer($bool)
 */
class ImportExport extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'MageWorx_CustomerPrices::datatransfer/import_export.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setUseContainer(true);
    }

    /**
     * Return CSS classes for the export customer prices form container (<div>)
     * as a string concatenated with a space
     *
     * @return string
     */
    public function getExportCustomerPricesClasses()
    {
        $exportCustomerPricesClasses = ['export-customerprices'];
        if ($this->getIsReadonly()) {
            $exportCustomerPricesClasses[] = 'box-left';
        } else {
            $exportCustomerPricesClasses[] = 'box-right';
        }

        $exportCustomerPricesClasses = implode(' ', $exportCustomerPricesClasses);

        return $exportCustomerPricesClasses;
    }
}
