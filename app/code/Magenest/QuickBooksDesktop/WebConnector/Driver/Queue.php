<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\WebConnector\Driver;

use Magenest\QuickBooksDesktop\WebConnector\Driver;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;

/**
 * Class Queue
 * @package Magenest\QuickBooksDesktop\WebConnector\Driver
 */
class Queue extends Driver
{
    /**
     * @return bool|int
     */
    public function getTotalsQueue()
    {
        $collection = $this->getCollection();
        $totals = $collection->getSize();
        if ($totals) {
            return $totals;
        }

        return false;
    }

    /**
     * Get Queue Collection
     *
     * @return \Magenest\QuickBooksDesktop\Model\ResourceModel\Queue\Collection
     */
    public function getCollection()
    {
        $companyId = $this->_queueHelper->getCompanyId();
        $collection = $this->_collectionFactory->create()
            ->addFieldToFilter('ticket_id', ['null' => true])
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('status', Status::STATUS_QUEUE)
            ->setOrder('priority', 'ASC');

        return $collection;
    }

    /**
     * @return \Magenest\QuickBooksDesktop\Model\Queue
     */
    public function getCurrentQueue()
    {
        $collection = $this->getCollection();

        return $collection->getFirstItem();
    }

    /**
     * @param $queue
     * @return string
     */
    public function prepareSendRequestXML($queue)
    {
        /** @var  \Magenest\QuickBooksDesktop\Model\Queue $queue */
        $action = $queue->getActionName();
        $typeQueue = $queue->getType();
        $queueEntityId = $queue->getData('entity_id');
        $companyId = $this->_queueHelper->getCompanyId();
        $model = null;

        if($typeQueue == 'ProductInv'){
			$product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($queueEntityId);
            
            $StockState = $this->_objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($queueEntityId);
            $bin_locations_arr = array();
            if($product->getData('bin_locations')){
                $bin_locations = $product->getData('bin_locations');
                $bin_locations_arr = explode (", ", $bin_locations);
            }
//$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/invetoryreq.log');
// $logger = new \Zend\Log\Logger();
// $logger->addWriter($writer);
// $logger->info($StockState->getQty());
            $qty = $StockState->getQty();
            if($qty <= 0){
                $qty = 0;
            }
            // $StockState = $this->_objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
             // $qty = $StockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
             // list id royal main 80000002-1563895799
			$collection = $this->getQuickBooksIDs($companyId, Type::QUEUE_PRODUCT, $queueEntityId);
			$xml = '<?xml version="1.0" encoding="utf-8"?>' .
            '<?qbxml version="13.0"?>' .
            '<QBXML>' .
            '<QBXMLMsgsRq onError="continueOnError">'.
			'<InventoryAdjustmentAddRq> <InventoryAdjustmentAdd>';
            $xml .= $this->multipleXml($this->_scopeConfig->getValue('qbdesktop/account_setting/cogs'), ['AccountRef', 'FullName']);
            $xml .= '<InventorySiteRef>  <FullName >Royal Main</FullName> </InventorySiteRef>';

			/*$xml .= '<InventoryAdjustmentLineAdd><ItemRef>'.$this->simpleXml($collection['list_id'], 'ListID').'<FullName >'.$product->getSku().'</FullName></ItemRef>'.
			'<QuantityAdjustment><NewQuantity>'.$qty.'</NewQuantity><InventorySiteLocationRef><FullName>Royal Main:AA01 A3</FullName></InventorySiteLocationRef></QuantityAdjustment></InventoryAdjustmentLineAdd>'. '<InventoryAdjustmentLineAdd><ItemRef>'.$this->simpleXml($collection['list_id'], 'ListID').'<FullName >'.$product->getSku().'</FullName></ItemRef>'.
            '<QuantityAdjustment><NewQuantity>'.$qty.'</NewQuantity><InventorySiteLocationRef><FullName>Royal Main:AA01 B1</FullName></InventorySiteLocationRef></QuantityAdjustment></InventoryAdjustmentLineAdd>';*/
            
            if(is_array($bin_locations_arr) && !empty($bin_locations_arr)){
                //foreach($bin_locations_arr as $bin_loc){
                   $xml_repeat = '<InventoryAdjustmentLineAdd><ItemRef>'.$this->simpleXml($collection['list_id'], 'ListID').'<FullName >'.$product->getSku().'</FullName></ItemRef>'.
                   '<QuantityAdjustment><NewQuantity>'.$qty.'</NewQuantity><InventorySiteLocationRef><FullName>Royal Main:'.trim($bin_locations_arr[0]).'</FullName></InventorySiteLocationRef></QuantityAdjustment></InventoryAdjustmentLineAdd>'; 
                //}

            }else{
                $xml_repeat = '<InventoryAdjustmentLineAdd><ItemRef>'.$this->simpleXml($collection['list_id'], 'ListID').'<FullName >'.$product->getSku().'</FullName></ItemRef>'.
                   '<QuantityAdjustment><NewQuantity>'.$qty.'</NewQuantity></QuantityAdjustment></InventoryAdjustmentLineAdd>'; 
                
            }
            

            $xml .= $xml_repeat.'</InventoryAdjustmentAdd></InventoryAdjustmentAddRq></QBXMLMsgsRq></QBXML>';
            //$xml .= '<InventorySiteRef>  <FullName >Royal Main</FullName> </InventorySiteRef>';

			/*$xml .= '<InventoryAdjustmentLineAdd><ItemRef>'.$this->simpleXml($collection['list_id'], 'ListID').'<FullName >'.$product->getSku().'</FullName></ItemRef>'.
			'<QuantityAdjustment><NewQuantity>'.$qty.'</NewQuantity></QuantityAdjustment></InventoryAdjustmentLineAdd>'.
			'</InventoryAdjustmentAdd></InventoryAdjustmentAddRq></QBXMLMsgsRq></QBXML>';*/
// 			$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/invetoryreq.log');
// $logger = new \Zend\Log\Logger();
// $logger->addWriter($writer);
// $logger->info($xml);
			return $xml;
		}
        
        //Start XML Request
        $xml = '<?xml version="1.0" encoding="utf-8"?>' .
            '<?qbxml version="13.0"?>' .
            '<QBXML>' .
            '<QBXMLMsgsRq onError="continueOnError">';
        try {
            if ($typeQueue == 'ItemOtherCharge') {
                $xml .= $this->getItemOtherChargeAddName($action);
            } elseif ($typeQueue == 'ItemDiscount') {
                $xml .= $this->getItemDiscountAddName($action);
            } elseif ($typeQueue == 'ShipMethod') {
                $xml .= $this->getShipMethodAddName($action, $queue->getPayment());
            } elseif ($typeQueue == 'Vendor') {
                $xml .= $this->getVendorAddName($action, $queue->getPayment());
            } else {
                $method = 'get' . $typeQueue . 'Model';
                $model = $this->$method();

                if (($action == 'CustomerMod')
                    || ($action == 'ItemInventoryMod')
                    || ($action == 'ItemNonInventoryMod')
                ) {
                    $xml .= '<' . $action . 'Rq>';
                    $xml .= '<' . $action . '>';
                    if ($action == 'CustomerMod') {
                        $collection = $this->getQuickBooksIDs($companyId, Type::QUEUE_CUSTOMER, $queueEntityId);
                    } else {
                        $collection = $this->getQuickBooksIDs($companyId, Type::QUEUE_PRODUCT, $queueEntityId);
                    }
                    $xml .= $this->simpleXml($collection['list_id'], 'ListID');
                    $xml .= $this->simpleXml($collection['edit_sequence'], 'EditSequence');
                    $xml .= $model->getXml($queueEntityId);

                    $xml = str_replace('SalesOrPurchase', 'SalesOrPurchaseMod', $xml);

                    $post1 = strpos($xml, '<QuantityOnHand>');
                    $post2 = strpos($xml, '</QuantityOnHand>');
                    if ($post1 !== false && $post2 !== false) {
                        $substr = substr($xml, $post1, $post2 - $post1 + 17);
                        $xml = str_replace($substr, '', $xml);
                    }
                } elseif ($typeQueue == 'PaymentMethod') {
                    $xml .= '<' . $action . 'Rq>';
                    $xml .= '<' . $action . '>';
                    $xml .= $model->getXml($queue->getPayment());
                } else {
                    $xml .= '<' . $action . 'Rq requestID="' . $queue->getId() . '">';
                    $rand = time() . rand(1, 500000);
                    if ($typeQueue == 'Product' || $typeQueue == 'Customer' || $typeQueue == 'Guest') {
                        $xml .= '<' . $action . '>';
                    } else {
                        $xml .= '<' . $action . ' defMacro="' . $rand . '">';
                    }
                    $xml .= $model->getXml($queueEntityId);
                }
                $xml .= '</' . $action . '>';
            }
            $xml .= '</' . $action . 'Rq>';
        } catch (\Exception $exception) {
            \Magento\Framework\App\ObjectManager::getInstance()
                ->create('Psr\Log\LoggerInterface')
                ->debug("ERROR: " . $exception->getMessage());
        }
        $xml .= '</QBXMLMsgsRq></QBXML>';

        \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Psr\Log\LoggerInterface')
            ->debug("ComeONnnn   " . print_r($xml, true) . "\n");

        return $xml;
    }

    protected function getItemOtherChargeAddName($action)
    {
        $xml = '<' . $action . 'Rq>';
        $xml .= '<' . $action . '>';
        $xml .= $this->simpleXml('Shipping', 'Name');
        $xml .= $this->multipleXml('Non', ['SalesTaxCodeRef', 'FullName']);
        $xml .= $this->multipleXml($this->_scopeConfig->getValue('qbdesktop/account_setting/cogs'), ['SalesOrPurchase', 'AccountRef', 'FullName']);
        $xml .= '</' . $action . '>';

        return $xml;
    }

    protected function getItemDiscountAddName($action)
    {
        $xml = '<' . $action . 'Rq>';
        $xml .= '<' . $action . '>';
        $xml .= $this->simpleXml('Discount', 'Name');
        $xml .= $this->multipleXml('Non', ['SalesTaxCodeRef', 'FullName']);
        $xml .= $this->multipleXml($this->_scopeConfig->getValue('qbdesktop/account_setting/cogs'), ['AccountRef', 'FullName']);
        $xml .= '</' . $action . '>';

        return $xml;
    }

    protected function getShipMethodAddName($action, $payment)
    {
        $xml = '<' . $action . 'Rq>';
        $xml .= '<' . $action . '>';
        $xml .= $this->simpleXml($payment, 'Name');
        $xml .= '</' . $action . '>';

        return $xml;
    }

    protected function getVendorAddName($action, $payment)
    {
        $xml = '<' . $action . 'Rq>';
        $xml .= '<' . $action . '>';
        $xml .= $this->simpleXml($payment, 'Name');
        $xml .= $this->simpleXml('true', 'IsSalesTaxAgency');
        $xml .= '</' . $action . '>';

        return $xml;
    }

    protected function getQuickBooksIDs($companyId, $type, $entityId)
    {
        $collection = $this->_map->getCollection()
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('entity_id', $entityId)
            ->getLastItem()->getData();
        return $collection;
    }

    /**
     * Customer Model Object
     *
     * @return \Magenest\QuickBooksDesktop\Model\QBXML\Customer
     */
    protected function getCustomerModel()
    {
        return $this->_objectManager->get('Magenest\QuickBooksDesktop\Model\QBXML\Customer');
    }

    /**
     * Customer Model Object
     *
     * @return \Magenest\QuickBooksDesktop\Model\QBXML\Customer
     */
    protected function getGuestModel()
    {
        return $this->_objectManager->get('Magenest\QuickBooksDesktop\Model\QBXML\Guest');
    }

    /**
     * Tax Model Object
     *
     * @return \Magenest\QuickBooksDesktop\Model\QBXML\Tax
     */
    protected function getPaymentMethodModel()
    {
        return $this->_objectManager->get('Magenest\QuickBooksDesktop\Model\QBXML\Payment');
    }

    /**
     * Tax Model Object
     *
     * @return \Magenest\QuickBooksDesktop\Model\QBXML\Tax
     */
    protected function getReceivePaymentModel()
    {
        return $this->_objectManager->get('Magenest\QuickBooksDesktop\Model\QBXML\ReceivePayment');
    }

    /**
     * Product Model Object
     *
     * @return \Magenest\QuickBooksDesktop\Model\QBXML\Customer
     */
    protected function getProductModel()
    {
        return $this->_objectManager->get('Magenest\QuickBooksDesktop\Model\QBXML\Item');
    }

    /**
     * Sales Order Model Object
     *
     * @return \Magenest\QuickBooksDesktop\Model\QBXML\SalesOrder
     */
    protected function getSalesOrderModel()
    {
        return $this->_objectManager->get('Magenest\QuickBooksDesktop\Model\QBXML\SalesOrder');
    }

    /**
     * Estimate Model Object
     *
     * @return \Magenest\QuickBooksDesktop\Model\QBXML\Estimate
     */
    protected function getCreditMemoModel()
    {
        return $this->_objectManager->get('Magenest\QuickBooksDesktop\Model\QBXML\CreditMemo');
    }


    /**
     * Invoice Model Object
     *
     * @return \Magenest\QuickBooksDesktop\Model\QBXML\Invoice
     */
    protected function getInvoiceModel()
    {
        return $this->_objectManager->get('Magenest\QuickBooksDesktop\Model\QBXML\Invoice');
    }
}
