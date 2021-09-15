<?php namespace BoostMyShop\UltimateReport\Block;

class Reports extends \Magento\Backend\Block\Template
{
    protected $_template = 'Reports.phtml';

    protected $_reports;
    protected $_reportsHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \BoostMyShop\UltimateReport\Helper\Reports $reportsHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_reportsHelper = $reportsHelper;
    }


    public function addReport($reportCode)
    {
        if ($report = $this->_reportsHelper->getReport($reportCode))
            $this->_reports[] = $report;
    }

    public function injectReport($report)
    {
        $this->_reports[] = $report;
    }

    public function getReports()
    {
        return $this->_reports;
    }

    public function renderReport($report)
    {
        $renderer = $this->_reportsHelper->getRenderer($report);
        if ($renderer)
            return $renderer->toHtml();
        else
            return "Unable to render report ".$report->getKey();
    }

}