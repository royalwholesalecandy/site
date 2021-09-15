<?php

namespace BoostMyShop\UltimateReport\Helper;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager;

class Reports
{
    protected $_reports = [];
    protected $_reportFactory;

    protected $objectManagerFactory;
    protected $objectManager;


    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        \BoostMyShop\UltimateReport\Model\ReportFactory $reportFactory
    )
    {
        $this->_reportFactory = $reportFactory;
        $this->objectManagerFactory = $objectManagerFactory;

        $this->loadReports();
    }

    public function getAllReports()
    {
        return $this->_reports;
    }

    public function getReport($reportCode)
    {
        if (isset($this->_reports[$reportCode]))
            return $this->_reports[$reportCode];
        else
            return false;
    }

    public function loadReports()
    {
        foreach($this->getReportFiles() as $file)
        {
            $this->extractReportsFromFile($file);
        }
    }


    protected function getReportFiles()
    {
        $dir = __DIR__.'/../Reports';
        $files = scandir($dir);
        $files = array_diff($files, ['.', '..']);
        foreach($files as $k => $v)
        {
            $files[$k] = $dir.'/'.$files[$k];
        }
        return $files;
    }

    protected function extractReportsFromFile($filePath)
    {
        $group = basename($filePath, ".xml");

        $nodes = simplexml_load_file($filePath);
        foreach ((array)$nodes as $k => $elt) {

            $report = $this->_reportFactory->create();
            $report->setData((array)$elt);
            $report->setKey($group.".".$k);

            $this->_reports[$group.".".$k] = $report;
        }

    }

    public function getRenderer($report)
    {
        $class = null;

        switch($report->getRenderer())
        {
            case 'line':
                $class = "\\BoostMyShop\\UltimateReport\\Block\\Report\\Renderer\\Line";
                break;
            case 'bar':
                $class = "\\BoostMyShop\\UltimateReport\\Block\\Report\\Renderer\\Bar";
                break;
            case 'table':
                $class = "\\BoostMyShop\\UltimateReport\\Block\\Report\\Renderer\\Table";
                break;
            case 'pie':
                $class = "\\BoostMyShop\\UltimateReport\\Block\\Report\\Renderer\\Pie";
                break;
        }

        if ($class)
        {
            $block = $this->getObjectManager()->create($class);
            $block->setReport($report);
            return $block;
        }
        else
            return false;
    }

    protected function getObjectManager()
    {
        if (null == $this->objectManager) {
            $area = FrontNameResolver::AREA_CODE;
            $this->objectManager = $this->objectManagerFactory->create($_SERVER);
            $appState = $this->objectManager->get('Magento\Framework\App\State');
            $appState->setAreaCode($area);
            $configLoader = $this->objectManager->get('Magento\Framework\ObjectManager\ConfigLoaderInterface');
            $this->objectManager->configure($configLoader->load($area));
        }
        return $this->objectManager;
    }

}
