<?php

namespace BoostMyShop\AdvancedStock\Plugin\Bundle\Model\ResourceModel\Selection;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Framework\App\ObjectManager;

//Magento 2.2 compatibility
class Collection extends \Magento\Bundle\Model\ResourceModel\Selection\Collection
{

    /**
     * Add website filter
     *
     * @return $this
     * @since 100.2.0
     */
    public function addQuantityFilter()
    {
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();

        $this->getSelect()
            ->joinInner(
                ['stock' => $this->getTable('cataloginventory_stock_status')],
                'selection.product_id = stock.product_id and stock.website_id = '.$websiteId,
                []
            )
            ->where(
                '(selection.selection_can_change_qty or selection.selection_qty <= stock.qty) and stock.stock_status'
            );
        return $this;
    }

}
