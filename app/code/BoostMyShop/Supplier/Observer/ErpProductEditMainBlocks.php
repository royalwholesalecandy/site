<?php

namespace BoostMyShop\Supplier\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class ErpProductEditMainBlocks implements ObserverInterface
{

    public function execute(EventObserver $observer)
    {
        $block = $observer->getEvent()->getblock();
        $product = $observer->getEvent()->getProduct();
        $layout = $observer->getEvent()->getLayout();

        $blockContent = $layout->createBlock('BoostMyShop\Supplier\Block\ErpProduct\Edit\Overview\OpenedPo');
        $blockContent->setProduct($product);
        $block->addBlock('Opened Purchase Orders', $blockContent->toHtml());

        return $this;
    }
}
