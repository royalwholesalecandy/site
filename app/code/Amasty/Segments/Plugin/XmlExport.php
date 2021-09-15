<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Plugin;

class XmlExport extends \Amasty\Segments\Plugin\AbstractExport
{
    /**
     * @var string
     */
    protected $exportType = 'xml';

    /**
     * @param \Magento\Ui\Model\Export\ConvertToXml $subject
     * @param \Closure $proceed
     * @return array|mixed
     */
    public function aroundGetXmlFile(
        \Magento\Ui\Model\Export\ConvertToXml $subject,
        \Closure $proceed
    ) {
        return $this->checkNamespace() ? $proceed() : $this->exportByType();
    }
}
