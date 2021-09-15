<?php

namespace BoostMyShop\Organizer\Model;


class ObjectType extends \Magento\Framework\Model\AbstractModel
{

    protected $_orderFactory;
    protected $_purchaseOrderFactory;
    protected $_supplierFactory;
    protected $_transferFactory;
    protected $_productFactory;
    protected $_invoiceFactory;
    protected $_config;
    protected $urlFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \BoostMyShop\Organizer\Model\Config $config,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \BoostMyShop\Supplier\Model\OrderFactory $purchaseOrderFactory,
        \BoostMyShop\Supplier\Model\SupplierFactory $supplierFactory,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \BoostMyShop\AdvancedStock\Model\TransferFactory $transferFactory,
        \BoostMyShop\AdvancedStock\Model\StockTakeFactory $stockTakeFactory,
        \BoostMyShop\Supplier\Model\InvoiceFactory $invoiceFactory,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);

        $this->_orderFactory = $orderFactory;
        $this->_purchaseOrderFactory = $purchaseOrderFactory;
        $this->_supplierFactory = $supplierFactory;
        $this->_productFactory = $productFactory;
        $this->_transferFactory = $transferFactory;
        $this->_stockTakeFactory = $stockTakeFactory;
        $this->_invoiceFactory = $invoiceFactory;
        $this->_config = $config;
        $this->urlFactory = $urlFactory;
    }

    private function getUrlInstance()
    {
        return $this->urlFactory->create();
    }

    public function getObjectLabel($objectType, $objectId)
    {

        $retour = '';
        switch ($objectType) {
            case \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_ORDER:
                $order = $this->_orderFactory->create()->load($objectId);
                $retour = __('Order').' #' . $order->getIncrementId();
                break;
            case \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_PURCHASE_ORDER:
                $purchaseOrder = $this->_purchaseOrderFactory->create()->load($objectId);
                $retour = __('Purchase Order').' #' . $purchaseOrder->getPoReference();
                break;
            case \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_SUPPLIER:
                $supplier = $this->_supplierFactory->create()->load($objectId);
                $retour = __('Supplier').' - ' . $supplier->getsup_name();
                break;
            case \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_ERP_PRODUCT:
                $product = $this->_productFactory->create()->load($objectId);
                $retour = __('ERP').' - ' . $product->getName();
                break;
            case \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_STOCK_TRANSFER:
                $transfer = $this->_transferFactory->create()->load($objectId);
                $retour = __('Stock Transfer').' #' . $transfer->getst_reference();
                break;
            case \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_STOCK_TAKE:
                $stocktake = $this->_stockTakeFactory->create()->load($objectId);
                $retour = __('Stock Take').' - ' . $stocktake->getsta_name();
                break;
            case \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_SUPPLIER_INVOICE:
                $invoice = $this->_invoiceFactory->create()->load($objectId);
                $retour = __('Supplier Invoice').' #' . $invoice->getBsiReference();
                break;
        }
        return $retour;
    }

    public function getObjectUrl($objectType, $objectId)
    {
        $retour = '';
        switch ($objectType) {
            case \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_ORDER:
                $retour = $this->getUrlInstance()->getUrl('sales/order/view', ['order_id'=>$objectId]);
                break;
            case \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_PURCHASE_ORDER:
                $retour = $this->getUrlInstance()->getUrl('supplier/order/edit', ['po_id'=>$objectId]);
                break;
            case \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_SUPPLIER:
                $retour = $this->getUrlInstance()->getUrl('supplier/supplier/edit', ['sup_id'=>$objectId]);
                break;
            case \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_ERP_PRODUCT:
                $retour = $this->getUrlInstance()->getUrl('erp/products/edit', ['id'=>$objectId]);
                break;
            case \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_STOCK_TRANSFER:
                $retour = $this->getUrlInstance()->getUrl('advancedstock/transfer/edit', ['id'=>$objectId]);
                break;
            case \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_STOCK_TAKE:
                $retour = $this->getUrlInstance()->getUrl('advancedstock/stocktake/edit', ['id'=>$objectId]);
                break;
            case \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_SUPPLIER_INVOICE:
                $retour = $this->getUrlInstance()->getUrl('supplier/invoice/edit', ['bsi_id'=>$objectId]);
                break;
        }
        return $retour;
    }
}
