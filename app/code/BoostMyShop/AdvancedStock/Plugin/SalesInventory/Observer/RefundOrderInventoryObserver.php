<?php

namespace BoostMyShop\AdvancedStock\Plugin\SalesInventory\Observer;


class RefundOrderInventoryObserver {

    protected $_stockMovement;
    protected $_extendedItemFactory;
    protected $_backendAuthSession;

    public function __construct (
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovement,
        \BoostMyShop\AdvancedStock\Model\ExtendedSalesFlatOrderItemFactory $extendedItemFactory
    )
    {
        $this->_stockMovement = $stockMovement;
        $this->_extendedItemFactory = $extendedItemFactory;
        $this->_backendAuthSession = $backendAuthSession;
    }

    public function aroundExecute (
        \Magento\SalesInventory\Observer\RefundOrderInventoryObserver $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer)
    {
        /* @var $creditmemo \Magento\Sales\Model\Order\Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        foreach ($creditmemo->getAllItems() as $item) {
            $qty = $item->getQty();
            if ($item->getBackToStock() && $qty > 0)
            {
                $this->createStockMovement($item, $qty, $creditmemo);
            }
        }
    }

    protected function createStockMovement ($item, $qty, $creditmemo)
    {
        /** @var  $extendedItem \BoostMyShop\AdvancedStock\Model\ExtendedSalesFlatOrderItem */
        $extendedItem = $this->_extendedItemFactory->create()->loadByItemId($item->getOrderItemId());
        $warehouseId = ($extendedItem->getesfoi_warehouse_id()) ? $extendedItem->getesfoi_warehouse_id() : 1;

        $userId = null;
        if ($this->_backendAuthSession->getUser())
            $userId = $this->_backendAuthSession->getUser()->getId();

        $this->_stockMovement->create()->create(
            $item->getProductId(),
            0,
            $warehouseId,
            $qty,
            \BoostMyShop\AdvancedStock\Model\StockMovement\Category::adjustment,
            __('Return to stock (Credit Memo #%1)', $creditmemo->getIncrementId()),
            $userId
        );
    }

}