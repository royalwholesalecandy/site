<?php

namespace BoostMyShop\UltimateReport\Model\Config\Source;

class Reports implements \Magento\Framework\Option\ArrayInterface
{

    protected $_reportHelper;

    public function __construct(
        \BoostMyShop\UltimateReport\Helper\Reports $reportHelper
    )
    {
        $this->_reportHelper = $reportHelper;
    }

    public function toOptionArray()
    {
        $options = array();

        $collection = $this->_reportHelper->getAllReports();
        foreach($collection as $item)
        {
            if ($item->getavailable_for_dashboard())
                $options[] = array('value' => $item->getKey(), 'label' => $item->getName());
        }

        return $options;
    }

    public function toArray()
    {

    }

}
