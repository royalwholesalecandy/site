<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Block\Adminhtml\ImportExport;

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
    protected $_template = 'MageWorx_CustomerGroupPrices::datatransfer/import_export.phtml';

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
     * Return CSS classes for the export customer group prices form container (<div>)
     * as a string concatenated with a space
     *
     * @return string
     */
    public function getExportCustomerGroupPricesClasses()
    {
        $exportCustomerGroupPricesClasses = ['export-customerprices'];
        if ($this->getIsReadonly()) {
            $exportCustomerGroupPricesClasses[] = 'box-left';
        } else {
            $exportCustomerGroupPricesClasses[] = 'box-right';
        }

        $exportCustomerGroupPricesClasses = implode(' ', $exportCustomerGroupPricesClasses);

        return $exportCustomerGroupPricesClasses;
    }
}
