<?php

namespace BoostMyShop\UltimateReport\Model\Report\Source\Supplier;

class SupplyNeedsPerStatus extends \BoostMyShop\UltimateReport\Model\Report\Source\AbstractSource
{
    protected $_replenishmentCollection;

    public function __construct(
        \BoostMyShop\Supplier\Model\ResourceModel\Replenishment\CollectionFactory $replenishmentCollection
    ) {
        $this->_replenishmentCollection = $replenishmentCollection;
    }

    public function getReportDatas($max = null)
    {
        $data = [];

        $collection = $this->_replenishmentCollection->create()->init();
        foreach($collection as $item)
        {
            if (!isset($data[$item->getReason()]))
                $data[$item->getReason()] = 0;
            $data[$item->getReason()] += $item->getqty_to_order();
        }

        $finalData = [];
        foreach($data as $k => $v)
            $finalData[] = ['x' => $k, 'y' => $v];
        return $finalData;
    }
}