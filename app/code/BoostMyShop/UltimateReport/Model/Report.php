<?php

namespace BoostMyShop\UltimateReport\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager;

class Report extends \Magento\Framework\DataObject
{
    protected $_ultimateReportRegistry;
    protected $_reportResource;
    protected $_deploymentConfig;
    protected $_logger;

    protected $objectManagerFactory;
    protected $objectManager;

    public function __construct(
        \BoostMyShop\UltimateReport\Model\Registry $ultimateReportRegistry,
        \BoostMyShop\UltimateReport\Model\ResourceModel\Report $reportResource,
        \BoostMyShop\UltimateReport\Helper\Logger $logger,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        ObjectManagerFactory $objectManagerFactory,
        array $data = []
    )
    {
        parent::__construct($data);

        $this->_ultimateReportRegistry = $ultimateReportRegistry;
        $this->_reportResource = $reportResource;
        $this->_deploymentConfig   =   $deploymentConfig;
        $this->objectManagerFactory = $objectManagerFactory;
        $this->_logger = $logger;
    }

    public function getReportDatas($max = null)
    {
        switch($this->getsource_type())
        {
            case 'model':
                $obj = $this->getObjectManager()->create($this->getsource_model());
                return $obj->getReportDatas();
                break;
            case 'sql':
            default:
                $sql = $this->injectFilters($this->getSql());
                $this->_logger->log("SQL Query for report ".$this->getName()." : ".$sql);
                return $this->_reportResource->runQuery($sql, $max);
                break;
        }
    }

    public function getSeries()
    {
        switch($this->getsource_type()) {
            case 'model':
                $obj = $this->getObjectManager()->create($this->getsource_model());
                return $obj->getSeries();
                break;
            case 'sql':
            default:
                $series = [];
                $serieNodes = $this->getData('series');
                if (!$serieNodes)
                    throw new \Exception("Unable to load series for report ".$this->getName());
                foreach($serieNodes as $serieNode)
                {
                    $data = (array)$serieNode;
                    $serie = ['label' => $data['label'], 'column' => $data['column']];
                    $series[] = $serie;
                }
                return $series;
                break;
        }


    }

    protected function injectFilters($sql)
    {
        $filters = $this->_ultimateReportRegistry->getFilters();
        foreach($filters as $k => $v) {
            if ($k == 'date_to')
                $v .= ' 23:59:00';
            $sql = str_replace("{" . $k . "}", $v, $sql);
        }

        $prefix = $this->_deploymentConfig->get('db/table_prefix');
        $sql = str_replace("{prefix}", $prefix, $sql);
        $sql = str_replace("{date_format}", "%d %b %y", $sql);

        return $sql;
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
