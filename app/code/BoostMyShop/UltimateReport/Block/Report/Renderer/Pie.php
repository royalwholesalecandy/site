<?php namespace BoostMyShop\UltimateReport\Block\Report\Renderer;

class Pie extends AbstractRenderer {

    protected $_template = 'Reports/Renderer/Pie.phtml';

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

    public function getSeries()
    {
        $serie = ['name' => $this->getYLabel(), 'data' => []];
        $datas = $this->getReportDatas();
        if ($datas)
        {
            foreach($datas as $data)
                $serie['data'][] = (int)$data['y'];
        }

        return [$serie];
    }

    public function getYLabel()
    {
        return $this->getReport()->getData('y_label');
    }

    public function getSeriesData()
    {
        $data = [];
        $reportData = $this->getReportDatas();

        if ($reportData)
        {
            foreach($this->getReportDatas() as $row)
            {
                $data[] = [$row['x'], (int)$row['y']];
            }
        }

        return $data;
    }

}
