<?php

namespace BoostMyShop\AdvancedStock\Plugin\CatalogInventory\Model\ResourceModel\Stock;

use Magento\Framework\DB\GenericMapper;

class StockCriteriaMapper extends \Magento\CatalogInventory\Model\ResourceModel\Stock\StockCriteriaMapper
{

    public function mapWebsiteFilter($website)
    {
        if ($website instanceof \Magento\Store\Model\Website)
            $website = $website->getId();

        $this->addFieldToFilter('main_table.website_id', $website);
    }


}
