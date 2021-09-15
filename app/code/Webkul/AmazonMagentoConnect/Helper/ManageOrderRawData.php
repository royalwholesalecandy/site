<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\AmazonMagentoConnect\Helper;

use Webkul\AmazonMagentoConnect\Model\AmazonTempData;
use Webkul\AmazonMagentoConnect\Api\AmazonTempDataRepositoryInterface;
use Webkul\AmazonMagentoConnect\Api\ProductMapRepositoryInterface;
use Webkul\AmazonMagentoConnect\Api\OrderMapRepositoryInterface;
use Webkul\AmazonMagentoConnect\Helper\ManageProductRawData;
use Webkul\AmazonMagentoConnect\Model\OrderMap;
use Webkul\AmazonMagentoConnect\Model\Accounts;

class ManageOrderRawData extends \Magento\Framework\App\Helper\AbstractHelper
{
    /*
    contain amazon client
    */
    private $amzClient;

    /**
     * @var AmazonTempData
     */
    private $amazonTempData;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Webkul\AmazonMagentoConnect\Logger\Logger
     */
    private $logger;

    /**
     * @var ProductMapRepositoryInterface
     */
    private $productMapRepo;

    /**
     * @var OrderMapRepositoryInterface
     */
    private $orderMapRepo;

    /**
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Data $helper
     * @param \Webkul\AmazonMagentoConnect\Helper\Order $orderHelper
     * @param AmazonTempData $amazonTempData
     * @param AmazonTempDataRepositoryInterface $amazonTempDataRepository
     * @param \Webkul\AmazonMagentoConnect\Logger\Logger $logger
     * @param ProductMapRepositoryInterface $productMapRepo
     * @param OrderMapRepositoryInterface $orderMapRepo
     * @param ManageProductRawData $manageProductRawData
     * @param OrderMap $orderMapRecord
     * @param Accounts $accounts
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Directory\Model\Region $region
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Data $helper,
        \Webkul\AmazonMagentoConnect\Helper\Order $orderHelper,
        AmazonTempData $amazonTempData,
        AmazonTempDataRepositoryInterface $amazonTempDataRepository,
        \Webkul\AmazonMagentoConnect\Logger\Logger $logger,
        ProductMapRepositoryInterface $productMapRepo,
        OrderMapRepositoryInterface $orderMapRepo,
        ManageProductRawData $manageProductRawData,
        OrderMap $orderMapRecord,
        Accounts $accounts,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Directory\Model\Region $region
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->amazonTempData = $amazonTempData;
        $this->amazonTempDataRepository = $amazonTempDataRepository;
        $this->logger = $logger;
        $this->productMapRepo = $productMapRepo;
        $this->orderMapRepo = $orderMapRepo;
        $this->orderHelper = $orderHelper;
        $this->manageProductRawData = $manageProductRawData;
        $this->orderMapRecord = $orderMapRecord;
        $this->accounts = $accounts;
        $this->backendSession = $backendSession;
        $this->productRepository = $productRepository;
        $this->region = $region;
    }

    /**
     * get amazon client
     *
     * @return object
     */
    public function getInitilizeAmazonClient()
    {
        if (!$this->amzClient) {
            $this->amzClient = $this->helper->getAmzClient();
        }
        $this->manageProductRawData->getInitilizeAmazonClient();
    }

    /**
     * get amazon order between ranage
     *
     * @param array $rangeData
     * @param boolean $viaCron
     * @return void | array
     */
    public function getFinalOrderReport($rangeData, $viaCron = false)
    {
        $this->getInitilizeAmazonClient();
        $collection = $this->accounts->getCollection()
                ->addFieldToFilter('entity_id', ['eq'=>$this->helper->accountId]);

        foreach ($collection as $account) {
            $orderParams = [];
            $notifications = null;
            $error = false;
            $errorData = '';
            $errorMsg = null;
            $response = [];
            $orderNextToken = $rangeData['next_token'];
            $orderParams['recordCount'] = '20';
            $toDate = new \DateTime($rangeData['order_to']);

            $fromDate = new \DateTime($rangeData['order_from']);

            try {
                $amzOrders = '';
                $count = 0;
                if (!empty($orderNextToken)) {
                    $orderLists = $this->amzClient->listOrdersByNextToken($orderNextToken);
                    if (isset($orderLists['ListOrdersByNextTokenResult']['NextToken'])) {
                        $this->backendSession
                            ->setData(
                                'order_next_token',
                                $orderLists['ListOrdersByNextTokenResult']['NextToken']
                            );
                    } else {
                        $orderNextToken = '';
                    }

                    if (isset($orderLists['ListOrdersByNextTokenResult']['Orders']['Order'])) {
                        $ordersArray = $orderLists['ListOrdersByNextTokenResult']['Orders']['Order'];
                        $amzOrders = isset($ordersArray[0])? $ordersArray : [$ordersArray];
                    }
                } else {
                    $orderLists = $this->amzClient
                            ->listOrders($fromDate, $toDate, $orderParams['recordCount']);
                    
                    if (isset($orderLists['ListOrdersResult']['NextToken'])) {
                        $orderNextToken = $orderLists['ListOrdersResult']['NextToken'];
                    }

                    if (isset($orderLists['ListOrdersResult']['Orders']['Order'])) {
                        $orderListArr = $orderLists['ListOrdersResult']['Orders']['Order'];
                        $amzOrders = isset($orderListArr[0])?$orderListArr : [$orderListArr];
                    }
                }

                if (!empty($amzOrders)) {
                    $notifications = $this->manageOrderData($amzOrders, $account->getId(), $viaCron);

                    $errorMsg = $errorMsg.$notifications['notification'];
                    $errorData = $errorData.$notifications['errorMsg'];
                    $count = $count + count($notifications['items']);
                } else {
                    if (isset($orderLists['error'])) {
                        $error = true;
                        $errorMsg = $orderLists['error'];
                    } else {
                        $error = true;
                        $errorMsg = __('No Amazon Order(s) Found');
                    }
                }
                
                $response['error_msg'] = $errorData;
                $response['next_token'] = $orderNextToken;
                $response['notification'] = $errorMsg;
                $response['data'] = $count;
            } catch (\Exception $e) {
                $response['notification'] = '';
                $response['data'] = '';
                $response['error'] = true;
                $response['error_msg'] = $e->getMessage();
                $response['next_token'] = '';
            }
            return $response;
        }
    }

    /**
     * manage order data
     * @param  array  $amzOrders
     * @param  boolean $accountId
     * @param  boolean $viaCron
     * @return array
     */
    public function manageOrderData($amzOrders, $accountId = false, $viaCron = false)
    {
        $this->getInitilizeAmazonClient();
        $items = [];
        $notifications = [];
        $errorMsg = '';
        $i=0;
        
        try {
            $tempAvlImported = $this->amazonTempData
                                ->getCollection()
                                ->addFieldToFilter('item_type', 'order')
                                ->getColumnValues('item_id');

            $alreadyMapped = $this->orderMapRecord
                            ->getCollection()
                            ->getColumnValues('amazon_order_id');
            $tempAvlImported =  array_merge($tempAvlImported, $alreadyMapped);

            foreach ($amzOrders as $key => $rawOrderData) {
                if (in_array($rawOrderData['AmazonOrderId'], $tempAvlImported)) {
                    continue;
                }
                $validOrderStatus = ['Shipped','PartiallyShipped','Unshipped'];
                if (!in_array($rawOrderData['OrderStatus'], $validOrderStatus)) {
                    continue;
                }
                
                /****/
                $firstname = 'Guest';
                $lastname = 'User';
                $shipPrice = 0;
                $shipMethod = __('From Amazon ');

                if (isset($rawOrderData['ShipServiceLevel'])) {
                    $shipMethod .= $rawOrderData['ShipServiceLevel'];
                }
                if (!isset($rawOrderData['ShippingAddress'])) {
                    continue;
                }

                $orderCmptDetails = $this->getExtraDetailsOfAmzOrder(
                    $rawOrderData['AmazonOrderId'],
                    $accountId,
                    $viaCron
                );
                if (empty($orderCmptDetails)) {
                    continue;
                }
                $orderItems = [];
                $shippingCharge = 0;
                $invalidOrder = false;
                foreach ($orderCmptDetails as $key => $amzorderItem) {
                    if (isset($amzorderItem['error_msg']) && !$amzorderItem['error_msg']) {
                        $productId = $amzorderItem['orderItems']['product_id'];
                        
                        if ($productId) {
                            $orderItems[] = [
                                        'product_id' => $amzorderItem['orderItems']['product_id'],
                                        'qty' => $amzorderItem['orderItems']['qty'],
                                        'price' => $amzorderItem['orderItems']['price'],
                                    ];
                            $shippingCharge = $shippingCharge +  $amzorderItem['shipping_price']['price'];
                        } else {
                            $errorMsg = $errorMsg.' Amazon order id : <b>'
                                            .$rawOrderData['AmazonOrderId']
                                            ."</b> not sync because Product <b>'"
                                            .$amzorderItem['title']
                                            .$amzorderItem['productAsin']
                                            ."'</b> not exist on your amazon <br />";
                            $this->logger->info($errorMsg);
                            $invalidOrder = true;
                        }
                    } else {
                        $this->logger->info(json_decode($orderCmptDetails));
                        $invalidOrder = true;
                    }
                }

                if ($invalidOrder) {
                    continue;
                }
                if (isset($rawOrderData['ShippingAddress']['Name'])) {
                    $buyerData = explode(" ", trim($rawOrderData['ShippingAddress']['Name']), 2);

                    $firstName = $buyerData[0];
                    $lastName = isset($buyerData[1])?$buyerData[1] : $buyerData[0];
                }
                /****/
                foreach ($rawOrderData['ShippingAddress'] as $key => $value) {
                    if ($value == '') {
                        $rawOrderData['ShippingAddress'][$key] = __('NA');
                        $rawOrderData['ShippingAddress']['save_in_address_book'] = 0;
                    }
                }
                if (!isset($rawOrderData['ShippingAddress']['CountryCode'])) {
                    $rawOrderData['ShippingAddress']['CountryCode'] = __('NA');
                }
                
                $tempOrder = [
                    'amz_order_id' => $rawOrderData['AmazonOrderId'],
                    'order_status' => $rawOrderData['OrderStatus'],
                    'currency_id' => $rawOrderData['OrderTotal']['CurrencyCode'],
                    'purchase_date' => $rawOrderData['PurchaseDate'],
                    'email' => $this->getBuyerEmail($rawOrderData),
                    'fulfillment_channel' => $rawOrderData['FulfillmentChannel'],
                    'shipping_address' => [
                        'firstname' => $firstName,
                        'lastname' => $lastName,
                        'street' => $this->getStreetAddress($rawOrderData),
                        'city' => $rawOrderData['ShippingAddress']['City'],
                        'country_id' => $rawOrderData['ShippingAddress']['CountryCode'],
                        'region' => $this->getOrderRegion($rawOrderData),
                        'postcode' => $rawOrderData['ShippingAddress']['PostalCode'],
                        'telephone' => '0000',
                        'fax' => '',
                        'vat_id' => '',
                        'save_in_address_book' => 1,
                    ],
                    'items' => $orderItems,
                    'shipping_service' => ['method' => $shipMethod,'cost' => $shippingCharge],
                ];
                if (!$viaCron) {
                    $dt = new \DateTime();
                    $currentDate = $dt->format('Y-m-d\TH:i:s');

                    $tempdata = [
                            'item_type' => 'order',
                            'item_id' => $tempOrder['amz_order_id'],
                            'item_data' => json_encode($tempOrder),
                            'created_at' => $currentDate,
                            'mage_amz_account_id' => $this->helper->accountId,
                            'purchase_date' => $rawOrderData['PurchaseDate'],
                            'fulfillment_channel' => $tempOrder['fulfillment_channel']
                        ];
                    
                    $tempOrderData = $this->amazonTempData;
                    $tempOrderData->setData($tempdata);
                    $item = $tempOrderData->save();
                    array_push($items, $item->getEntityId());
                } else {
                    $mapedOrder = $this->orderMapRepo
                            ->getCollectionByAmzOrderId($rawOrderData['AmazonOrderId'])
                            ->getFirstItem();
                    if ($mapedOrder->getEntityId()) {
                        continue;
                    }
                    $date = new \DateTime($rawOrderData['PurchaseDate']);
                    $purchaseDate = $date->format('Y-m-d H:i:s');

                    $mapedOrder = $this->orderMapRepo
                            ->getCollectionByAmzOrderId($tempOrder['amz_order_id'])
                            ->getFirstItem();


                    $orderData = $this->orderHelper
                            ->createAmazonOrderAtMage($tempOrder);

					/**
					 * 2019-12-24 Dmitry Fedyuk https://www.upwork.com/fl/mage2pro
					 * "Prevent the `Webkul_AmazonMagentoConnect` module from logging successful events":
					 * https://github.com/royalwholesalecandy/core/issues/64
					 */
                    //$this->logger->info(' Order created At Magento ');
                    //$this->logger->info(json_encode($orderData));

                    $items[$i]['order'] = $orderData;

                    if (isset($orderData['order_id']) && $orderData['order_id']) {
                        $data = [
                            'amazon_order_id' => $tempOrder['amz_order_id'],
                            'mage_order_id' => $orderData['order_id'],
                            'status' => $tempOrder['order_status'],
                            'mage_amz_account_id'   => $this->helper->accountId,
                            'purchase_date' => $purchaseDate,
                            'fulfillment_channel' => $tempOrder['fulfillment_channel']
                        ];

                        $record = $this->orderMapRecord;
                        $record->setData($data)->save();
                    }
                    $i++;
                }
            }
            $notifications['errorMsg'] = false;
            $notifications['notification'] = $errorMsg;
            $notifications['items'] = $items;
        } catch (\Exception $e) {
            $notifications['notification'] = '';
            $notifications['items'] = '';
            $notifications['errorMsg'] = $e->getMessage();
        }
        return $notifications;
    }

    /**
     * get street address of amazon order
     *
     * @param array $rawOrderData
     * @return string
     */
    public function getStreetAddress($rawOrderData)
    {
        $streetLineCount = $this->helper->getStreetLineNumber();
        $streetAddress = '';
        $addressLine1 = isset($rawOrderData['ShippingAddress']['AddressLine1'])? $rawOrderData['ShippingAddress']['AddressLine1'] : '';
        $addressLine2 = isset($rawOrderData['ShippingAddress']['AddressLine2'])? $rawOrderData['ShippingAddress']['AddressLine2'] : '';
        
        if (!isset($rawOrderData['ShippingAddress']['AddressLine2'])) {
            $streetAddress = $addressLine1;
        } else {
            if ($streetLineCount > 1) {
                $addressJoin = !empty($addressLine1) ? $addressLine1."\r\n" : '';
                $streetAddress = $addressJoin.$addressLine2;
            } else {
                $streetAddress = isset($addressLine1)?$addressLine1 : $addressLine2;
            }
        }
        return $streetAddress;
    }

    /**
     * getOrderRegion
     * @param array $shippingAddress
     * @return string
     */
    private function getOrderRegion($rawOrderData)
    {
        $region = isset($rawOrderData['ShippingAddress']['StateOrRegion']) ? $rawOrderData['ShippingAddress']['StateOrRegion'] : $rawOrderData['ShippingAddress']['City'];
        $addState = [];
        $requiredStates = $this->helper->getRequiredStateList();
        $requiredStatesArray = explode(',', $requiredStates);
        if (in_array($rawOrderData['ShippingAddress']['CountryCode'], $requiredStatesArray)) {
            $countryId = $rawOrderData['ShippingAddress']['CountryCode'];
            $regionData = $this->region->loadByCode($region, $countryId);
            if ($regionData->getRegionId()) {
                $region = $regionData->getRegionId();
            } else {
                $regionData = $this->region->loadByName('other', $countryId);
                if ($regionData->getRegionId()) {
                    $region = $regionData->getRegionId();
                } else {
                    $addState['country_id'] = $countryId;
                    $addState['code'] = 'other';
                    $addState['default_name'] = 'other';
                    $region = $this->region->setData($addState)->save()->getRegionId();
                }
            }
        }
        return $region;
    }

    /**
     * get buyer email from amazon order
     *
     * @param [type] $orderData
     * @return void
     */
    public function getBuyerEmail($rawOrderData)
    {
        if (!isset($rawOrderData['BuyerEmail'])) {
            $buyerEmail = str_replace(' ', '', $rawOrderData['BuyerName']);
            $buyerEmail = $buyerEmail.'@marketplace.amazon.com';
        } else {
            $buyerEmail = $rawOrderData['BuyerEmail'];
        }
        return $buyerEmail;
    }
    
    /**
     * get complete details of order linke shipping, item cost, and product.
     * @param  string $AmazonOrderId
     * @return array
     */
    public function getExtraDetailsOfAmzOrder($amazonOrderId, $accountId, $viaCron)
    {
        $amzOrderItems = $this->amzClient->listOrderItems($amazonOrderId);
        $orderItems = [];
        if (count($amzOrderItems)) {
            foreach ($amzOrderItems as $key => $amzOrder) {
                $amzOrderDetails = [];
                $errorMsg = true;
                if (isset($amzOrder['ASIN'])) {
                    if (!isset($amzOrder['ItemPrice'])) {
                        continue;
                    }
                    $amzProCollection =  $this->productMapRepo
                            ->getCollectionByAmzProductId($amzOrder['ASIN'])->getFirstItem();
                    $amzOrderDetails['productAsin'] = $amzOrder['ASIN'];
                    $amzOrderDetails['title'] = $amzOrder['Title'];
                    if ($amzProCollection->getEntityId()) {
                        $errorMsg = false;
                        $productId = null;
                        $productType = null;
                        $productId = $amzProCollection->getMagentoProId();
                        $productType = $amzProCollection->getProductType();
                        $amzOrderDetails['orderItems'] = [
                            'product_id' => $productId,
                            'product_type' => $productType,
                            'qty' => $amzOrder['QuantityOrdered'],
                            'price' => $amzOrder['ItemPrice']['Amount']/$amzOrder['QuantityOrdered'],
                            'curreny_code' => $amzOrder['ItemPrice']['CurrencyCode'],
                        ];
                        $amzOrderDetails['error_msg'] = $errorMsg;
                    } else {
                        $result = $this->manageProductRawData->getProductByAsin($amzOrder['ASIN'], false, true);

                        if (!empty($result['error'])) {
                            $product = $this->productRepository->get($result['sku']);
                            if ($product->getId()) {
                                $errorMsg = false;
                                $amzOrderDetails['orderItems'] = [
                                    'product_id' => $product->getId(),
                                    'product_type' => $product->getTypeId(),
                                    'qty' => $amzOrder['QuantityOrdered'],
                                    'price' => $amzOrder['ItemPrice']['Amount']/$amzOrder['QuantityOrdered'],
                                    'curreny_code' => $amzOrder['ItemPrice']['CurrencyCode'],
                                ];
                                $amzOrderDetails['error_msg'] = $errorMsg;
                            } else {
                                $errorMsg = true;
                                $amzOrderDetails['error_msg'] = $errorMsg;

                                $this->logger->info(' order Product Item Not found in amazon!. Details are  ');
                                $this->logger->info(json_encode($amzOrderDetails));
                            }
                        } else {
                            $amzProCollection =  $this->productMapRepo
                                ->getCollectionByAmzProductId($amzOrder['ASIN'])->getFirstItem();
                            $errorMsg = false;

                            $amzOrderDetails['orderItems'] = [
                                'product_id' => $amzProCollection->getMagentoProId(),
                                'product_type' => $amzProCollection->getProductType(),
                                'qty' => $amzOrder['QuantityOrdered'],
                                'price' => $amzOrder['ItemPrice']['Amount']/$amzOrder['QuantityOrdered'],
                                'curreny_code' => $amzOrder['ItemPrice']['CurrencyCode'],
                            ];
                            $amzOrderDetails['error_msg'] = $errorMsg;
                        }
                    }
                    if (!$errorMsg) {
                        if (isset($amzOrder['ShippingPrice'])) {
                            $amzOrderDetails['shipping_price'] = [
                                'price' => $amzOrder['ShippingPrice']['Amount'],
                                'curreny_code' => $amzOrder['ShippingPrice']['CurrencyCode'],
                            ];
                        } else {
                            $amzOrderDetails['shipping_price'] = [
                                'price' => '0',
                                'curreny_code' => $this->helper->getAmazonCurrencyCode(),
                            ];
                        }
                    }
                } else {
                    $amzOrderDetails['error_msg'] = $errorMsg;
                }
                $orderItems[] = $amzOrderDetails;
            }
        }
        return $orderItems;
    }
}
