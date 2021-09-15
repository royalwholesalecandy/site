<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Plugin;

class CsvExport extends \Amasty\Segments\Plugin\AbstractExport
{
    /**
     * @var string
     */
    protected $exportType = 'csv';

    /**
     * @param \Magento\Ui\Model\Export\ConvertToCsv $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function aroundGetCsvFile(
        \Magento\Ui\Model\Export\ConvertToCsv $subject,
        \Closure $proceed
    ) {
        return $this->checkNamespace() ? $proceed() : $this->exportByType();
    }
}
