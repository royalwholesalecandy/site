<?php namespace BoostMyShop\UltimateReport\Observer;


class SupplierEditTabs implements \Magento\Framework\Event\ObserverInterface
{

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $supplier = $observer->getEvent()->getSupplier();
        $tabs = $observer->getEvent()->getTabs();
        $layout = $observer->getEvent()->getLayout();

        $tabs->addTab(
            'ultimatereport',
            [
                'label' => __('Reports'),
                'content' => $layout->createBlock('BoostMyShop\UltimateReport\Block\Supplier\Edit\Tabs\Report')->setSupplier($supplier)->toHtml()
            ]
        );


        return $this;

    }

}