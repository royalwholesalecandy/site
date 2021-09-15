<?php

namespace BoostMyShop\AdvancedStock\Plugin\Block\Adminhtml\Product\Edit\Tab;

class Inventory
{
    public function afterGetTemplate(\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Inventory $subject, $result)
    {
        return 'BoostMyShop_AdvancedStock::Product/Edit/Tab/inventory.phtml';
    }

}