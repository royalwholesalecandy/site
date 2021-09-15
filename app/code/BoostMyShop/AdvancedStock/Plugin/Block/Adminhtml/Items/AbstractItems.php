<?php

namespace BoostMyShop\AdvancedStock\Plugin\Block\Adminhtml\Items;

class AbstractItems {

    protected $_stockRegistry;

    public function __construct(\Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry)
    {
        $this->_stockRegistry = $stockRegistry;
    }

    /**
     * Force to display Return to Stock checkbox
     * even with "Decrease Stock When Order Is Placed" option set to "No"
     * @return int
     */
    public function aroundCanReturnToStock ()
    {
        return 1;
    }

    /**
     * Display Return to Stock checkbox only if "Manage Stock" option is enabled
     * @param \Magento\Sales\Block\Adminhtml\Items\AbstractItems $subject
     * @param callable $proceed
     * @param \Magento\Sales\Model\Order\Creditmemo\Item|null $item
     * @return bool|int
     */
    public function aroundCanReturnItemToStock(
        \Magento\Sales\Block\Adminhtml\Items\AbstractItems $subject,
        \Closure $proceed,
        $item = null)
    {
        if (null !== $item) {
            $stockItem = $this->_stockRegistry->getStockItem(
                $item->getOrderItem()->getProductId(),
                $item->getOrderItem()->getStore()->getWebsiteId()
            );

            return $stockItem->getManageStock();
        }

        return 1;
    }

}