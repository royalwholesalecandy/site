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
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\TypeQuery;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Config\Source\Version;

/**
 * Class Inventory
 * @package Magenest\QuickBooksDesktop\WebConnector\Driver
 */
class Inventory extends Driver
{
	public $totalRequest = true;
	/**
     * @return bool|int
     */
    public function getTotalsQueue()
    {
        return $this->getSystemVal();
    }
	/**
     * @return \Config value
     */
	public function getSystemVal(){
		
		return true;
	}
    /**
     * @return \Magenest\QuickBooksDesktop\Model\Queue
     */
    public function getCurrentQueue()
    {
        return $this->totalRequest;
    }
    /**
     * @param $customQueue
     * @return string
     */
    public function prepareSendRequestXML($customQueue)
    {

        /** @var \Magenest\QuickBooksDesktop\Model\CustomQueue $customQueue */
        $version = $this->_queueHelper->getQuickBooksVersion();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $action = 'ItemInventoryQuery';
		$timeZone = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
		$todate = $timeZone->date()->format('c');
		$dateTimeMinutesAgo = new \DateTime("300 minutes ago");
		$fromdate = $dateTimeMinutesAgo->format("c");
        //$operation = $model->getOperation();
        //date('c', strtotime("-30 minutes"))
        
        // $xml = $this->getTransactionXml();
		// $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/inventoryadjustment.log');
		// $logger = new \Zend\Log\Logger();
		// $logger->addWriter($writer);
		// $logger->info($xml);
		// //$this->totalRequest = false;
        // return $xml;
		
        $xml = '<?xml version="1.0" encoding="utf-8"?>' .
            '<?qbxml version="13.0"?>' .
            '<QBXML>' .
            '<QBXMLMsgsRq onError="stopOnError">';

        
            $xml .= '<' . $action . 'Rq>';
            //$xml .= '<RefNumber>' . '00138' . '</RefNumber>';
            //$xml .= '<RefNumber>' . '1156' . '</RefNumber>';
            //$xml .= '<MaxReturned>' . '1' . '</MaxReturned>';
            
            $xml .= '<ActiveStatus>' . 'ActiveOnly' . '</ActiveStatus>';
			//$xml .= '<ModifiedDateRangeFilter>';
            //$xml .= '<FromModifiedDate>' . date('c', strtotime("-2 days")). '</FromModifiedDate>';
            //$xml .= '<ToModifiedDate>' . date('c', strtotime("+1 days")) . '</ToModifiedDate>';
            //$xml .= '</ModifiedDateRangeFilter>';
            //$xml .= '<FullName >1047C</FullName>';
			//$xml .= '<IncludeLineItems>' . true . '</IncludeLineItems>';
			//$xml .= '<IncludeLinkedTxns>' . true . '</IncludeLinkedTxns>';
			//$xml .= '<IncludeRetElement>SalesOrderLineRetList</IncludeRetElement>';
            $xml .= '</' . $action . 'Rq>';
            $xml .= '</QBXMLMsgsRq></QBXML>';
		//$xml = $this->getTransactionXml();
		// $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/inventory.log');
		// $logger = new \Zend\Log\Logger();
		// $logger->addWriter($writer);
		// $logger->info($xml);
		$this->totalRequest = false;
        return $xml;
    }
	public function getTransactionXml(){
		$action = 'InventoryAdjustmentQuery';
		$xml = '<?xml version="1.0" encoding="utf-8"?>' .
            '<?qbxml version="13.0"?>' .
            '<QBXML>' .
            '<QBXMLMsgsRq onError="stopOnError">';

        
            $xml .= '<' . $action . 'Rq>';
			//$xml .= '<RefNumber>' . '00137' . '</RefNumber>';
            $xml .= '<MaxReturned>' . '2000' . '</MaxReturned>';
            
           // $xml .= '<ActiveStatus>' . 'All' . '</ActiveStatus>';
			//$xml .= '<TransactionTypeFilter>';
           // $xml .= '<TxnTypeFilter>SalesOrder</TxnTypeFilter>';
           // $xml .= '<ToModifiedDate>' . date('c') . '</ToModifiedDate>';
			//$xml .= '</TransactionTypeFilter>';
			//$xml .= '<TransactionItemFilter>';
           // $xml .= '<ItemTypeFilter>Sales</ItemTypeFilter>';
           // $xml .= '<ToModifiedDate>' . date('c') . '</ToModifiedDate>';
			//$xml .= '</TransactionItemFilter>';
			$xml .= '<IncludeLineItems>' . true . '</IncludeLineItems>';
			//$xml .= '<IncludeLinkedTxns>' . true . '</IncludeLinkedTxns>';
			//$xml .= '<IncludeRetElement>Detail</IncludeRetElement>';
            $xml .= '</' . $action . 'Rq>';
            $xml .= '</QBXMLMsgsRq></QBXML>';
		return $xml;
	}
}
