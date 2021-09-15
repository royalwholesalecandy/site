<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-report-builder
 * @version   1.0.24
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportBuilder\Block\Adminhtml;

use Magento\Backend\Block\Template;

class BuilderJs extends Template
{
    protected $_template = 'Mirasvit_ReportBuilder::builder_js.phtml';

    private   $urlBuilder;

    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        $this->urlBuilder = $context->getUrlBuilder();

        parent::__construct($context, $data);
    }

    public function getConfig()
    {
        return [];
    }

    public function getDuplicateUrl()
    {
        return $this->urlBuilder->getUrl('reportBuilder/api/duplicate');
    }

    public function getSaveUrl()
    {
        return $this->urlBuilder->getUrl('reportBuilder/api/save');
    }

    public function getDeleteUrl()
    {
        return $this->urlBuilder->getUrl('reportBuilder/api/delete');
    }
}