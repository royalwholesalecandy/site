<?php namespace BoostMyShop\UltimateReport\Block\Report\Renderer;

class Line extends AbstractRenderer {

    protected $_template = 'Reports/Renderer/Line.phtml';

    public function getCategories()
    {
        $categories = [];
        $datas = $this->getReportDatas();
        foreach($datas as $item)
        {
            if (!in_array($item['x'], $categories))
                $categories[] = $item['x'];
        }

        return $categories;
    }

    public function getSeriesData()
    {
        $seriesData = [];

        foreach($this->getSeries() as $serie)
        {
            $serieData = ['name' => $serie['label'], 'data' => []];
            $datas = $this->getReportDatas();
            foreach($datas as $data)
                $serieData['data'][] = (int)$data[$serie['column']];
            $seriesData[] = $serieData;
        }

        return $seriesData;
    }

    public function getYLabel()
    {
        return $this->getReport()->getData('y_label');
    }

}
