<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class WebsiteSaveAfter implements ObserverInterface
{
    protected $_stockFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\CatalogInventory\StockFactory $stockFactory
    ) {
        $this->_stockFactory = $stockFactory;
    }

    public function execute(EventObserver $observer)
    {

        $website = $observer->getEvent()->getWebsite();

        $stock = $this->_stockFactory->create();
        if (!$stock->getIdFromWebsiteId($website->getId()))
        {
            $stock->createStock($website->getId());
            $stockId = $stock->getIdFromWebsiteId($website->getId());
            $stock->createStockItemRecords($stockId, $website->getId());
        }

        return $this;
    }


}
