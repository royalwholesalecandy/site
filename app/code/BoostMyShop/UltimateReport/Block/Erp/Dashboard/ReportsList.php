<?php

namespace BoostMyShop\UltimateReport\Block\Erp\Dashboard;

class ReportsList extends \Magento\Backend\Block\Template
{
    protected $_config;
    protected $_reports;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = [],
        \BoostMyShop\UltimateReport\Model\Config $config,
        \BoostMyShop\UltimateReport\Model\Config\Source\Reports $reports
    ) {
        $this->_config = $config;
        $this->_reports = $reports;

        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        $reports = $this->_config->getDashboardReports();
        if (count($reports) > 1)
        {
            foreach($reports as $report)
                $this->getParentBlock()->addReport($report);
        }
        else
        {
            foreach($this->_reports->toOptionArray() as $report)
                $this->getParentBlock()->addReport($report['value']);
        }

    }



}