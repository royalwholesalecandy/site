<?php

namespace BoostMyShop\Supplier\Model;

use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

class Replenishment extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Supplier\Model\ResourceModel\Replenishment');
    }

    public function loadByProductId($productId)
    {
        $this->_getResource()->loadByProductId($this, $productId);
        return $this;
    }

}