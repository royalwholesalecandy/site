<?php

namespace BoostMyShop\AvailabilityStatus\Model;

class AvailabilityStatus
{
    protected $_logger;
    protected $_config;
    protected $_stockRegistry;
    protected $_purchaseOrderProductCollectionFactory;
    protected $_supplierProductCollectionFactory;

    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Product\CollectionFactory $purchaseOrderProductCollectionFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\Product\CollectionFactory $supplierProductCollectionFactory,
        \BoostMyShop\AvailabilityStatus\Helper\Logger $logger,
        \BoostMyShop\AvailabilityStatus\Model\Config $config
    ){
        $this->_config = $config;
        $this->_logger = $logger;
        $this->_stockRegistry = $stockRegistry;
        $this->_purchaseOrderProductCollectionFactory = $purchaseOrderProductCollectionFactory;
        $this->_supplierProductCollectionFactory = $supplierProductCollectionFactory;
    }

    /**
     * @param $product
     * @param $storeId
     * @return array|bool : array with keys date & message (string)
     */
    public function getAvailability($product, $storeId)
    {
        if (!$product->isAvailable())
        {
            return $this->getOutOfStockMessage($product, $storeId);
        }

        if ($this->getStockQty($product, $storeId) > 0)
            return $this->getInStockMessage($product, $storeId);

        return $this->getBackorderMessage($product, $storeId);
    }

    protected function getInStockMessage($product, $storeId)
    {
        $result = [];
        $result['date'] = date('Y-m-d');

        $css = $this->_config->getSetting('instock/css', $storeId);
        $label = $this->_config->getSetting('instock/label', $storeId);
        $result['message'] = '<div class="'.$css.'">'.$label.'</div>';

        $this->_logger->log('Product #'.$product->getId().' : date='.$result['date'].', message='.strip_tags($result['message']));

        return $result;
    }

    public function getOutOfStockMessage($product, $storeId)
    {
        $result = [];

        $css = $this->_config->getSetting('outofstock/css', $storeId);
        if ($this->_config->getSetting('outofstock/use_po', $storeId))
            $result = $this->getPoMessage($product, $storeId, 'outofstock');

        if (!$result)
        {
            $label = $this->_config->getSetting('outofstock/label', $storeId);
            $result['message'] =  $label;
            $result['date'] = date('Y-m-d', time() + 3600 * 24 * 365);
        }

        $result['message'] =  '<div class="'.$css.'">'.$result['message'].'</div>';

        $this->_logger->log('Product #'.$product->getId().' : date='.$result['date'].', message='.strip_tags($result['message']));

        return $result;
    }

    protected function getBackorderMessage($product, $storeId)
    {
        $result = false;

        if ($this->_config->getSetting('backorder/use_po', $storeId))
            $result = $this->getPoMessage($product, $storeId);

        if (($this->_config->getSetting('backorder/use_lead_time', $storeId)) && (!$result))
            $result = $this->getLeadTimeMessage($product, $storeId);

        $css = $this->_config->getSetting('backorder/css', $storeId);
        if (!$result) {
            $result = [];
            $result['date'] = date('Y-m-d', time() + 3600 * 24 * 180);
            $result['message'] = '<div class="'.$css.'">'.$this->_config->getSetting('backorder/label', $storeId).'</div>';

            $this->_logger->log('Product #'.$product->getId().' : date='.$result['date'].', message='.strip_tags($result['message']));
        }
        else
            $result['message'] = '<div class="'.$css.'">'.$result['message'].'</div>';

        return $result;
    }

    protected function getPoMessage($product, $storeId, $stockMode = 'backorder')
    {
        $collection = $this->_purchaseOrderProductCollectionFactory
                            ->create()
                            ->addProductFilter($product->getId())
                            ->addExpectedFilter()
                            ->addRealEta()
                            ->addOrderStatusFilter(\BoostMyShop\Supplier\Model\Order\Status::expected)
                            ->addFieldToFilter('po_eta', ['gt' => date('Y-m-d')])
        ;

        foreach($collection as $item)
        {
            $result = [];
            $result['date'] = $item->getreal_eta();

            $label = $this->_config->getSetting($stockMode.'/po_label', $storeId);
            $eta = strtotime($item->getreal_eta());
            $letters = ['d','D','j','l','N','S','w','z','W','F','m','M','n','t','Y','y','r'];
            foreach($letters as $letter)
                $label = str_replace('{'.$letter.'}', date($letter, $eta), $label);

            $result['message'] = $label;

            $this->_logger->log('Product #'.$product->getId().' : use PO #'.$item->getpop_po_id());
            $this->_logger->log('Product #'.$product->getId().' : date='.$result['date'].', message='.strip_tags($result['message']));

            return $result;
        }

        $this->_logger->log('Product #'.$product->getId().' : no PO available');

        return false;
    }

    protected function getLeadTimeMessage($product, $storeId)
    {
        $collection = $this->_supplierProductCollectionFactory->create()->getSuppliers($product->getId());

        foreach($collection as $item)
        {
            if ($item->getsup_shipping_delay())
            {
                $result = [];
                $result['date'] = date('Y-m-d', time() + 3600 * 24 * $item->getsup_shipping_delay());
                $result['message'] = $this->getLeadTimeRangeMessage($item->getsup_shipping_delay(), $storeId);

                $this->_logger->log('Product #'.$product->getId().' : use supplier #'.$item->getsup_id());
                $this->_logger->log('Product #'.$product->getId().' : date='.$result['date'].', message='.strip_tags($result['message']));

                return $result;
            }

        }

        $this->_logger->log('Product #'.$product->getId().' : no supplier available');

        return false;
    }

    protected function getStockQty($product)
    {
        $value = $this->_stockRegistry->getStockStatus($product->getId(), $product->getStore()->getWebsiteId())->getQty();
        $this->_logger->log('Product #'.$product->getId().' : stock='.$value);
        return $value;
    }

    protected function getLeadTimeRangeMessage($leadTime, $storeId)
    {
        for($i=0;$i<10;$i++)
        {
            $from = $this->_config->getSetting('backorder/from_'.$i, $storeId);
            $to = $this->_config->getSetting('backorder/to_'.$i, $storeId);
            if (($from <= $leadTime) && ($leadTime <= $to))
                return $this->_config->getSetting('backorder/message_'.$i, $storeId);
        }
    }

}