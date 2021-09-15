<?php namespace BoostMyShop\UltimateReport\Block\Report\Renderer;

abstract class AbstractRenderer extends \Magento\Backend\Block\Template {

    public function hasReportData()
    {
        return (count($this->getReportDatas(2)) > 0);
    }

    public function onClick()
    {

    }

    public function exportJs()
    {

    }

    public function getReportDatas($max = null)
    {
        $collection = $this->getReport()->getReportDatas($max);
        return $collection;
    }

    public function getDivId()
    {
        $id = 'report_'.$this->getReport()->getKey();
        $id = str_replace(".", "_", $id);
        return $id;
    }


    public function getSeries()
    {
        return $this->getReport()->getSeries();
    }

}
