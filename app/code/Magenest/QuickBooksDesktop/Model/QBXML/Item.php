<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Model\QBXML;

use Magento\Catalog\Model\Product as ProductModel;
use Magenest\QuickBooksDesktop\Model\QBXML;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;

/**
 * Class Customer
 *
 * @package Magenest\QuickBooksDesktop\Model\QBXML
 */
class Item extends QBXML
{
    /**
     * @var ProductModel
     */
    protected $_product;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;


    /**
     * @var QueueHelper
     */
    protected $_queueHelper;

    /**
     * Item constructor.
     * @param ProductModel $product
     * @param ScopeConfigInterface $scopeConfig
     * @param ObjectManagerInterface $objectManager
     * @param QueueHelper $queueHelper
     */
    public function __construct(
        ProductModel $product,
        ScopeConfigInterface $scopeConfig,
        ObjectManagerInterface $objectManager,
        QueueHelper $queueHelper
    ) {
        parent::__construct($objectManager);
        $this->_product = $product;
        $this->_scopeConfig = $scopeConfig;
        $this->_queueHelper = $queueHelper;
        $this->_version = $this->_queueHelper->getQuickBooksVersion();
    }

    /**
     * Get XML using sync to QBD
     * @param $id
     * @return string
     */
    public function getXml($id)
    {
        $model = $this->_product->load($id);
        $qty = $model->getExtensionAttributes()->getStockItem()->getQty();
        $xml = $this->simpleXml(substr(str_replace(['&','”','\'','<', '>','"'], ' ', $model->getSku()), 0, 30), 'Name');
		//$xml .= $this->multipleXml($model->getData('upc'), ['BarCode','BarCodeValue']);
		if($model->getData('upc')){
			$xml .= '<BarCode><BarCodeValue>'.$model->getData('upc').'</BarCodeValue><AssignEvenIfUsed>'.true.'</AssignEvenIfUsed><AllowOverride>'.true.'</AllowOverride></BarCode>';
		}
        
        $price = $model->getPrice();
        $finalPrice = $model->getFinalPrice();
        $type = $model->getTypeId();
        $custom_cost_price = 0.00;
        if($model->getData('custom_cost_price')){
            $custom_cost_price = $model->getData('custom_cost_price');

        }

        if($model->getData('bin_locations')){
            $bin_locations = $model->getData('bin_locations');

        }
        $bin_locations_arr = array();
        if($model->getData('bin_locations')){
            $bin_locations = $model->getData('bin_locations');
            $bin_locations_arr = explode (", ", $bin_locations);
        }
        if($bin_locations_arr[0]!=''){
            $qty = 0;
        }
        $qty = $qty ? $qty : 0;

        if ($qty > 0 || $type == 'simple' || $type == 'virtual' || $type == 'giftcard' || $type == 'downloadable') {
            $xml .= $this->simpleXml(strip_tags(str_replace(['&','”','\'','<', '>','"'], ' ', $model->getName())), 'SalesDesc');
            $xml .= $this->simpleXml($finalPrice, 'SalesPrice');
            $xml .= $this->multipleXml($this->getAccountName(), ['IncomeAccountRef','FullName']);
            $xml .= $this->simpleXml(strip_tags(str_replace(['&','”','\'','<', '>','"'], ' ', $model->getName())), 'PurchaseDesc');
            //$xml .= $this->simpleXml($price, 'PurchaseCost');
            $xml .= $this->simpleXml($custom_cost_price, 'PurchaseCost');
            $xml .= $this->multipleXml($this->getAccountName('cogs'), ['COGSAccountRef','FullName']);
            $xml .= $this->multipleXml($this->getAccountName('asset'), ['AssetAccountRef','FullName']);
            $xml .= $this->simpleXml($qty, 'QuantityOnHand');
        } else {
            $xml .= '<SalesOrPurchase>';
            $xml .= $this->simpleXml(strip_tags(str_replace(['&', '”', '\'', '<', '>', '"'], ' ', $model->getName())), 'Desc');
            $xml .= $this->simpleXml($price, 'Price');
            $xml .= $this->multipleXml($this->getAccountName('expense'), ['AccountRef', 'FullName']);
            $xml .= '</SalesOrPurchase>';
        }
		// $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/product.log');
		// $logger = new \Zend\Log\Logger();
		// $logger->addWriter($writer);
		// $logger->info($xml);
        return $xml;
    }


    /**
     * @param string $type
     * @return mixed
     */
    protected function getAccountName($type = 'income')
    {
        $path = 'qbdesktop/account_setting/'.$type;

        return $this->_scopeConfig->getValue($path);
    }
}
