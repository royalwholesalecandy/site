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



namespace Mirasvit\ReportBuilder\Service;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponentFactory;
use Mirasvit\ReportBuilder\Api\Data\ReportInterface;
use Mirasvit\ReportBuilder\Model\ReportInstanceFactory;

class BuilderService
{
    private $reportInstanceFactory;

    private $registry;

    private $uiComponentFactory;

    public function __construct(
        ReportInstanceFactory $reportInstanceFactory,
        Registry $registry,
        UiComponentFactory $uiComponentFactory
    ) {
        $this->reportInstanceFactory = $reportInstanceFactory;
        $this->registry              = $registry;
        $this->uiComponentFactory    = $uiComponentFactory;
    }

    public function getReportInstance(ReportInterface $report)
    {
        $instance = $this->reportInstanceFactory->create();
        $instance->setName($report->getTitle())
            ->setIdentifier($report->getId());

        return $instance;
    }
}
