<?php

namespace BoostMyShop\Supplier\Model\Product;

class AverageBuyingPrice
{
    protected $_receptionItemCollectionFactory;
    protected $_logger;

    public function __construct(
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Reception\Item\CollectionFactory $receptionItemCollectionFactory,
        \BoostMyShop\Supplier\Helper\Logger $logger
    ){
        $this->_receptionItemCollectionFactory = $receptionItemCollectionFactory;
        $this->_logger = $logger;
    }


    public function calculateValue($productId, $quantity)
    {
        $sum = 0;
        $count = 0;

        $receptions = $this->getReceptions($productId);
        $this->_logger->log('Calculate cost for product #'.$productId.' : '.count($receptions).' receptions found');
        foreach($receptions as $item)
        {
            $buyingPrice = ($item->getpop_price_base() + $item->getpop_extended_cost_base());
            $quantityToUse = min($quantity, $item->getpop_qty_received());
            if ($quantityToUse > 0)
            {
                $sum += $quantityToUse * $buyingPrice;
                $count += $quantityToUse;
                $quantity -= $quantityToUse;

                $this->_logger->log('Calculate cost for product #'.$productId.' : consider reception for quantity '.$quantityToUse.' and price '.$buyingPrice);
            }
        }

        if ($count > 0)
            return ($sum / $count);

    }

    protected function getReceptions($productId)
    {
        $receptions = $this->_receptionItemCollectionFactory->create()->addProductFilter($productId)->addOrderProductDetails()->setOrder('pori_id');
        return $receptions;
    }

}