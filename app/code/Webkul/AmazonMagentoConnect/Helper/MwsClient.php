<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Helper;

use Webkul\AmazonMagentoConnect\Api\AmazonTempDataRepositoryInterface;
use DateTime;
use DateTimeZone;
use League\Csv\Reader;
use League\Csv\Writer;
use SplTempFileObject;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Spatie\ArrayToXml\ArrayToXml;

class MwsClient extends \Magento\Framework\Model\AbstractModel
{
    const SIGNATURE_METHOD = 'HmacSHA256';
    const SIGNATURE_VERSION = '2';
    const DATE_FORMAT = "Y-m-d\TH:i:s.\\0\\0\\0\\Z";
    const APPLICATION_NAME = 'Webkul/MwsClient';
    private $config = [
        'Seller_Id' => null,
        'Marketplace_Id' => null,
        'Access_Key_ID' => null,
        'Secret_Access_Key' => null,
        'MWSAuthToken' => null,
        'Application_Version' => '0.0.*'
    ];
    private $MarketplaceIds = [
        'A2EUQ1WTGCTBG2' => 'mws.amazonservices.ca',
        'ATVPDKIKX0DER' => 'mws.amazonservices.com',
        'A1AM78C64UM0Y8' => 'mws.amazonservices.com.mx',
        'A1PA6795UKMFR9' => 'mws-eu.amazonservices.com',
        'A1RKKUPIHCS9HS' => 'mws-eu.amazonservices.com',
        'A13V1IB3VIYZZH' => 'mws-eu.amazonservices.com',
        'A21TJRUUN4KGV' => 'mws.amazonservices.in',
        'APJ6JRA9NG5V4' => 'mws-eu.amazonservices.com',
        'A1F83G8C2ARO7P' => 'mws-eu.amazonservices.com',
        'A1VC38T7YXB528' => 'mws.amazonservices.jp',
        'AAHKV2X7AFYLW' => 'mws.amazonservices.com.cn',
        'A39IBJ37TRP1C6' => 'mws.amazonservices.com.au',
        'A2Q3Y263D00KWC' => 'mws.amazonservices.com'
    ];
    private $debugNextFeed = false;

    private $client = null;

    public function __construct(array $config)
    {
        foreach ($config as $key => $value) {
            if (array_key_exists($key, $this->config)) {
                $this->config[$key] = $value;
            }
        }
        $requiredKeys = [
            'Marketplace_Id', 'Seller_Id', 'Access_Key_ID', 'Secret_Access_Key'
        ];
        foreach ($requiredKeys as $key) {
            if (empty($this->config[$key])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase('Required field ' . $key . ' is not set')
                );
            }
        }
        if (!isset($this->MarketplaceIds[$this->config['Marketplace_Id']])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Invalid Marketplace Id')
            );
        }
        $this->config['Application_Name'] = self::APPLICATION_NAME;
        $this->config['Region_Host'] = $this->MarketplaceIds[$this->config['Marketplace_Id']];
        $this->config['Region_Url'] = 'https://' . $this->config['Region_Host'];
    }
    /**
     * Call this method to get the raw feed instead of sending it
     */
    public function debugNextFeed()
    {
        $this->debugNextFeed = true;
    }
    /**
     * A method to quickly check if the supplied credentials are valid
     * @return boolean
     */
    public function validateCredentials()
    {
        try {
            $response = $this->ListOrderItems('validate');
            if (isset($response['Error']['Message']) &&
            $response['Error']['Message'] == 'Invalid AmazonOrderId: validate') {
                return true;
                ;
            } else {
                return false;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage() == 'Invalid AmazonOrderId: validate') {
                return true;
            } else {
                return false;
            }
        }
    }
    /**
     * Returns the current competitive price of a product, based on ASIN.
     * @param array [$asinArray = []]
     * @return array
     */
    public function getCompetitivePricingForASIN($asinArray = [])
    {
        if (count($asinArray) > 20) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Maximum amount of ASIN\'s for this call is 20')
            );
        }
        $counter = 1;
        $query = [
            'MarketplaceId' => $this->config['Marketplace_Id']
        ];
        foreach ($asinArray as $key) {
            $query['ASINList.ASIN.' . $counter] = $key;
            $counter++;
        }
        $response = $this->request(
            'GetCompetitivePricingForASIN',
            $query
        );
        if (isset($response['GetCompetitivePricingForASINResult'])) {
            $response = $response['GetCompetitivePricingForASINResult'];
            if (array_keys($response) !== range(0, count($response) - 1)) {
                $response = [$response];
            }
        } else {
            return [];
        }
        $array = [];
        foreach ($response as $product) {
            if (isset($product['Product']['CompetitivePricing']['CompetitivePrices']['CompetitivePrice']['Price'])) {
                $array[$product['Product']['Identifiers']['MarketplaceASIN']['ASIN']] = $product['Product']['CompetitivePricing']['CompetitivePrices']['CompetitivePrice']['Price'];
            }
        }
        return $array;
    }
    
        /**
         * Returns the current competitive price of a product, based on SKU.
         * @param array [$skuArray = []]
         * @return array
         */
    public function getCompetitivePricingForSKU($skuArray = [])
    {
        if (count($skuArray) > 20) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Maximum amount of SKU\'s for this call is 20')
            );
        }
        $counter = 1;
        $query = [
            'MarketplaceId' => $this->config['Marketplace_Id']
        ];
        foreach ($skuArray as $key) {
            $query['SellerSKUList.SellerSKU.' . $counter] = $key;
            $counter++;
        }
        $response = $this->request(
            'GetCompetitivePricingForSKU',
            $query
        );
        return $response;
    }
    /**
     * Returns lowest priced offers for a single product, based on ASIN.
     * @param string $asin
     * @param string [$ItemCondition = 'New'] Should be one in: New, Used, Collectible, Refurbished, Club
     * @return array
     */
    public function getLowestPricedOffersForASIN($asin, $ItemCondition = 'New')
    {
        $query = [
            'ASIN' => $asin,
            'MarketplaceId' => $this->config['Marketplace_Id'],
            'ItemCondition' => $ItemCondition
        ];
        return $this->request('GetLowestPricedOffersForASIN', $query);
    }
    /**
     * Returns pricing information for your own offer listings, based on SKU.
     * @param array  [$skuArray = []]
     * @param string [$ItemCondition = null]
     * @return array
     */
    public function getMyPriceForSKU($skuArray = [], $ItemCondition = null)
    {
        if (count($skuArray) > 20) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Maximum amount of SKU\'s for this call is 20')
            );
        }
        $counter = 1;
        $query = [
            'MarketplaceId' => $this->config['Marketplace_Id']
        ];
        if (!empty($ItemCondition)) {
            $query['ItemCondition'] = $ItemCondition;
        }
        foreach ($skuArray as $key) {
            $query['SellerSKUList.SellerSKU.' . $counter] = $key;
            $counter++;
        }
        $response = $this->request(
            'GetMyPriceForSKU',
            $query
        );
        return $response;
    }
    /**
     * Returns pricing information for your own offer listings, based on ASIN.
     * @param array [$asinArray = []]
     * @param string [$ItemCondition = null]
     * @return array
     */
    public function getMyPriceForASIN($asinArray = [], $ItemCondition = null)
    {
        if (count($asinArray) > 20) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Maximum amount of SKU\'s for this call is 20')
            );
        }
        $counter = 1;
        $query = [
            'MarketplaceId' => $this->config['Marketplace_Id']
        ];
        if (!empty($ItemCondition)) {
            $query['ItemCondition'] = $ItemCondition;
        }
        foreach ($asinArray as $key) {
            $query['ASINList.ASIN.' . $counter] = $key;
            $counter++;
        }
        $response = $this->request(
            'GetMyPriceForASIN',
            $query
        );
        if (isset($response['GetMyPriceForASINResult'])) {
            $response = $response['GetMyPriceForASINResult'];
            if (array_keys($response) !== range(0, count($response) - 1)) {
                $response = [$response];
            }
        } else {
            return [];
        }
        $array = [];
        foreach ($response as $product) {
            if (isset($product['@attributes']['status']) && $product['@attributes']['status'] == 'Success' && isset($product['Product']['Offers']['Offer'])) {
                $array[$product['@attributes']['ASIN']] = $product['Product']['Offers']['Offer'];
            } else {
                $array[$product['@attributes']['ASIN']] = false;
            }
        }
        return $array;
    }
    /**
     * Returns pricing information for the lowest-price active offer listings for up to 20 products, based on ASIN.
     * @param array [$asinArray = []] array of ASIN values
     * @param array [$ItemCondition = null] Should be one in: New, Used, Collectible, Refurbished, Club. Default: All
     * @return array
     */
    public function getLowestOfferListingsForASIN($asinArray = [], $ItemCondition = null)
    {
        if (count($asinArray) > 20) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase*('Maximum amount of ASIN\'s for this call is 20')
            );
        }
        $counter = 1;
        $query = [
            'MarketplaceId' => $this->config['Marketplace_Id']
        ];
        if (!empty($ItemCondition)) {
            $query['ItemCondition'] = $ItemCondition;
        }
        foreach ($asinArray as $key) {
            $query['ASINList.ASIN.' . $counter] = $key;
            $counter++;
        }
        $response = $this->request(
            'GetLowestOfferListingsForASIN',
            $query
        );
        if (isset($response['GetLowestOfferListingsForASINResult'])) {
            $response = $response['GetLowestOfferListingsForASINResult'];
            if (array_keys($response) !== range(0, count($response) - 1)) {
                $response = [$response];
            }
        } else {
            return [];
        }
        $array = [];
        foreach ($response as $product) {
            if (isset($product['Product']['LowestOfferListings']['LowestOfferListing'])) {
                $array[$product['Product']['Identifiers']['MarketplaceASIN']['ASIN']] = $product['Product']['LowestOfferListings']['LowestOfferListing'];
            } else {
                $array[$product['Product']['Identifiers']['MarketplaceASIN']['ASIN']] = false;
            }
        }
        return $array;
    }

    /**
     * Returns orders created or updated during a time frame that you specify.
     * @param object DateTime $from
     * @param boolean $allMarketplaces, list orders from all marketplaces
     * @param array $states, an array containing orders states you want to filter on
     * @param string $FulfillmentChannel
     * @return array
     */
    public function listOrders(\DateTime $from, \DateTime $to, $maxResultsPerPage, $allMarketplaces = false, $states = [
        'Unshipped','PartiallyShipped', 'Shipped'], $FulfillmentChannel = 'All')
    {
        $query = [
            'CreatedAfter' => gmdate(self::DATE_FORMAT, $from->getTimestamp()),
            'CreatedBefore' => gmdate(self::DATE_FORMAT, $to->getTimestamp()),
            'MaxResultsPerPage' => $maxResultsPerPage
            // 'FulfillmentChannel.Channel.1' => $FulfillmentChannel
        ];
        $counter = 1;
        foreach ($states as $status) {
            $query['OrderStatus.Status.' . $counter] = $status;
            $counter++;
        }
        if ($allMarketplaces == true) {
            $counter = 1;
            foreach ($this->MarketplaceIds as $key => $value) {
                $query['MarketplaceId.Id.' . $counter] = $key;
                $counter++;
            }
        }
        $response = $this->request(
            'ListOrders',
            $query
        );
        return $response;
    }

        /**
         * Returns orders created or updated during a time frame that you specify.
         * @param object DateTime $from
         * @param boolean $allMarketplaces, list orders from all marketplaces
         * @param array $states, an array containing orders states you want to filter on
         * @param string $FulfillmentChannel
         * @return array
         */
    public function listOrdersByNextToken($orderNextToken)
    {
        $query = [
            'NextToken' => $orderNextToken
        ];
        $response = $this->request(
            'ListOrdersByNextToken',
            $query
        );
        return $response;
    }
    /**
     * Returns an order based on the AmazonOrderId values that you specify.
     * @param string $AmazonOrderId
     * @return array if the order is found, false if not
     */
    public function getOrder($AmazonOrderId)
    {
        $response = $this->request('GetOrder', [
            'AmazonOrderId.Id.1' => $AmazonOrderId
        ]);
        if (isset($response['GetOrderResult']['Orders']['Order'])) {
            return $response['GetOrderResult']['Orders']['Order'];
        } else {
            return false;
        }
    }
    /**
     * Returns order items based on the AmazonOrderId that you specify.
     * @param string $AmazonOrderId
     * @return array
     */
    public function listOrderItems($AmazonOrderId)
    {
        $response = $this->request('ListOrderItems', [
            'AmazonOrderId' => $AmazonOrderId
        ]);
        if (isset($response['Error']['Message'])) {
            return $response;
        }
        $result = array_values($response['ListOrderItemsResult']['OrderItems']);
        if (isset($result[0]['QuantityOrdered'])) {
            return $result;
        } else {
            return $result[0];
        }
    }
    /**
     * Returns the parent product categories that a product belongs to, based on SellerSKU.
     * @param string $SellerSKU
     * @return array if found, false if not found
     */
    public function getProductCategoriesForSKU($SellerSKU)
    {
        $result = $this->request('GetProductCategoriesForSKU', [
            'MarketplaceId' => $this->config['Marketplace_Id'],
            'SellerSKU' => $SellerSKU
        ]);
        if (isset($result['GetProductCategoriesForSKUResult']['Self'])) {
            return $result['GetProductCategoriesForSKUResult']['Self'];
        } else {
            return false;
        }
    }
    /**
     * Returns the parent product categories that a product belongs to, based on ASIN.
     * @param string $ASIN
     * @return array if found, false if not found
     */
    public function getProductCategoriesForASIN($ASIN)
    {
        $result = $this->request('GetProductCategoriesForASIN', [
            'MarketplaceId' => $this->config['Marketplace_Id'],
            'ASIN' => $ASIN
        ]);
        if (isset($result['GetProductCategoriesForASINResult']['Self'])) {
            return $result['GetProductCategoriesForASINResult']['Self'];
        } else {
            return false;
        }
    }
    /**
     * Returns a list of products and their attributes, based on a list of ASIN, GCID, SellerSKU, UPC, EAN, ISBN, and JAN values.
     * @param array $asinArray A list of id's
     * @param string [$type = 'ASIN']  the identifier name
     * @return array
     */
    public function getMatchingProductForId(array $asinArray, $type = 'ASIN')
    {
        $asinArray = array_unique($asinArray);
        if (count($asinArray) > 5) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Maximum number of id\'s = 5')
            );
        }
        $counter = 1;
        $array = [
            'MarketplaceId' => $this->config['Marketplace_Id'],
            'IdType' => $type
        ];
        foreach ($asinArray as $asin) {
            $array['IdList.Id.' . $counter] = $asin;
            $counter++;
        }
        $response = $this->request(
            'GetMatchingProductForId',
            $array,
            null,
            true
        );
        $languages = [
            'de-DE', 'en-EN', 'es-ES', 'fr-FR', 'it-IT', 'en-US'
        ];
        $replace = [
            '</ns2:ItemAttributes>' => '</ItemAttributes>'
        ];
        foreach ($languages as $language) {
            $replace['<ns2:ItemAttributes xml:lang="' . $language . '">'] = '<ItemAttributes><Language>' . $language . '</Language>';
        }
        $replace['ns2:'] = '';
        $response = $this->xmlToArray(strtr($response, $replace));
        return $response;
    }

    /**
     * Returns a list of products and their attributes, based on a list of ASIN, GCID, SellerSKU, UPC, EAN, ISBN, and JAN values.
     * @param array $asinArray A list of id's
     * @param string [$type = 'ASIN']  the identifier name
     * @return array
     */
    public function getMatchingProduct(array $asinArray)
    {
        $asinArray = array_unique($asinArray);

        if (count($asinArray) > 5) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Maximum number of id\'s = 5')
            );
        }

        $counter = 1;
        $array = [
            'MarketplaceId' => $this->config['Marketplace_Id']
        ];

        foreach ($asinArray as $asin) {
            $array['ASINList.ASIN.' . $counter] = $asin;
            $counter++;
        }

        $response = $this->request(
            'GetMatchingProduct',
            $array,
            null,
            true
        );

        $languages = [
            'de-DE', 'en-EN', 'es-ES', 'fr-FR', 'it-IT', 'en-US'
        ];

        $replace = [
            '</ns2:ItemAttributes>' => '</ItemAttributes>'
        ];

        foreach ($languages as $language) {
            $replace['<ns2:ItemAttributes xml:lang="' . $language . '">'] = '<ItemAttributes><Language>' . $language . '</Language>';
        }

        $replace['ns2:'] = '';

        $response = $this->xmlToArray(strtr($response, $replace));
        return $response;
    }
    /**
     * Returns a list of products and their attributes, ordered by relevancy, based on a search query that you specify.
     * @param string $query the open text query
     * @param string [$query_context_id = null] the identifier for the context within which the given search will be performed. see: http://docs.developer.amazonservices.com/en_US/products/Products_QueryContextIDs.html
     * @return array
     */
    public function listMatchingProducts($query, $query_context_id = null)
    {
        if (trim($query) == "") {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Missing query')
            );
        }
        $array = [
            'MarketplaceId' => $this->config['Marketplace_Id'],
            'Query' => urlencode($query),
            'QueryContextId' => $query_context_id
        ];
        $response = $this->request(
            'ListMatchingProducts',
            $array,
            null,
            true
        );
        $languages = [
            'de-DE', 'en-EN', 'es-ES', 'fr-FR', 'it-IT', 'en-US'
        ];
        $replace = [
            '</ns2:ItemAttributes>' => '</ItemAttributes>'
        ];
        foreach ($languages as $language) {
            $replace['<ns2:ItemAttributes xml:lang="' . $language . '">'] = '<ItemAttributes><Language>' . $language . '</Language>';
        }
        $replace['ns2:'] = '';
        $response = $this->xmlToArray(strtr($response, $replace));
        if (isset($response['ListMatchingProductsResult'])) {
            return $response['ListMatchingProductsResult'];
        } else {
            return ['ListMatchingProductsResult'=>[]];
        }
    }
    /**
     * Returns a list of reports that were created in the previous 90 days.
     * @param array [$ReportTypeList = []]
     * @return array
     */
    public function getReportList($ReportTypeList = [])
    {
        $array = [];
        $counter = 1;
        if (count($ReportTypeList)) {
            foreach ($ReportTypeList as $ReportType) {
                $array['ReportTypeList.Type.' . $counter] = $ReportType;
                $counter++;
            }
        }
        return $this->request('GetReportList', $array);
    }
    /**
     * Returns your active recommendations for a specific category or for all categories for a specific marketplace.
     * @param string [$RecommendationCategory = null] One of: Inventory, Selection, Pricing, Fulfillment, ListingQuality, GlobalSelling, Advertising
     * @return array/false if no result
     */
    public function listRecommendations($RecommendationCategory = null)
    {
        $query = [
            'MarketplaceId' => $this->config['Marketplace_Id']
        ];
        if (!empty($RecommendationCategory)) {
            $query['RecommendationCategory'] = $RecommendationCategory;
        }
        $result = $this->request('ListRecommendations', $query);
        if (isset($result['ListRecommendationsResult'])) {
            return $result['ListRecommendationsResult'];
        } else {
            return false;
        }
    }
    /**
     * Returns a list of marketplaces that the seller submitting the request can sell in, and a list of participations that include seller-specific information in that marketplace
     * @return array
     */
    public function listMarketplaceParticipations()
    {
        $result = $this->request('ListMarketplaceParticipations');
        if (isset($result['ListMarketplaceParticipationsResult'])) {
            return $result['ListMarketplaceParticipationsResult'];
        } else {
            return $result;
        }
    }
    /**
     * Update a product's stock quantity
     * @param array $array array containing sku as key and quantity as value
     * @return array feed submission result
     */
    public function updateStock(array $array)
    {
        $feed = [
            'MessageType' => 'Inventory',
            'Message' => []
        ];
        foreach ($array as $key => $product) {
            $feed['Message'][] = [
                'MessageID' => rand(),
                'OperationType' => 'Update',
                'Inventory' => $product
            ];
        }

        return $this->submitFeed('_POST_INVENTORY_AVAILABILITY_DATA_', $feed);
    }
    /**
     * Update a product's stock quantity
     *
     * @param array $array array containing arrays with next keys: [sku, quantity, latency]
     * @return array feed submission result
     */
    public function updateStockWithFulfillmentLatency(array $array)
    {
        $feed = [
            'MessageType' => 'Inventory',
            'Message' => []
        ];
        foreach ($array as $item) {
            $feed['Message'][] = [
                'MessageID' => rand(),
                'OperationType' => 'Update',
                'Inventory' => [
                    'SKU' => $item['sku'],
                    'Quantity' => (int) $item['quantity'],
                    'FulfillmentLatency' => $item['latency']
                ]
            ];
        }
        return $this->submitFeed('_POST_INVENTORY_AVAILABILITY_DATA_', $feed);
    }
    /**
     * Update a product's price
     * @param array $standardprice an array containing sku as key and price as value
     * @param array $salesprice an optional array with sku as key and value consisting of an array with key/value pairs for SalePrice, StartDate, EndDate
     * Dates in \DateTime object
     * Price has to be formatted as XSD Numeric Data Type (http://www.w3schools.com/xml/schema_dtypes_numeric.asp)
     * @return array feed submission result
     */
    public function updatePrice(array $standardprice, array $saleprice = null)
    {
        $feed = [
            'MessageType' => 'Price',
            'Message' => []
        ];
        foreach ($standardprice as $key => $product) {
            $feed['Message'][] = [
                'MessageID' => rand(),
                'Price' => [
                    'SKU' => $product['sku'],
                    'StandardPrice' => [
                        '_value' => strval($product['price']),
                        '_attributes' => [
                            'currency' => 'DEFAULT'
                        ]
                    ]
                ]
            ];
            // if (isset($saleprice[$sku]) && is_array($saleprice[$sku])) {
            //     $feed['Message'][count($feed['Message']) - 1]['Price']['Sale'] = [
            //         'StartDate' => $saleprice[$sku]['StartDate']->format(self::DATE_FORMAT),
            //         'EndDate' => $saleprice[$sku]['EndDate']->format(self::DATE_FORMAT),
            //         'SalePrice' => [
            //             '_value' => strval($saleprice[$sku]['SalePrice']),
            //             '_attributes' => [
            //                 'currency' => 'DEFAULT'
            //             ]]
            //     ];
            // }
        }
        return $this->submitFeed('_POST_PRODUCT_PRICING_DATA_', $feed);
    }

    /**
     * post amazon product from magento
     *
     * @param array $array
     * @return void
     */
    public function postImages(array $array)
    {
        $feed = [
            'MessageType' => 'ProductImage',
            'Message' => []
        ];
        foreach ($array as $key => $product) {
            $feed['Message'][] = [
                'MessageID' => rand(),
                'OperationType' => 'Update',
                'ProductImage' => [
                    'SKU' => $product['sku'],
                    'ImageType' => $product['image_type'],
                    'ImageLocation' => $product['image_location'],
                ]
            ];
        }
        return $this->submitFeed('_POST_PRODUCT_IMAGE_DATA_', $feed);
    }

    /**
     * post amazon product from magento
     *
     * @param array $array
     * @return void
     */
    public function postProduct(array $array)
    {
        $feed = [
            'MessageType' => 'Product',
            'Message' => []
        ];
        foreach ($array as $key => $product) {
            $feed['Message'][] = [
                'MessageID' => rand(),
                'OperationType' => 'Update',
                'Product' => [
                    'SKU' => $product['sku'],
                    'StandardProductID' => [
                        'Type' => $product['type'],
                        'Value' => $product['value']
                    ],
                    'DescriptionData' => [
                        'Title' => $product['Title'],
                        'Description' => $product['description'],
                        'Battery' => [
                            'AreBatteriesIncluded'  => 'false',
                            'AreBatteriesRequired' => 'false',
                        ],
                        'SupplierDeclaredDGHZRegulation' => 'not_applicable',
                    ]
                ]
            ];
            if (!empty($product['condition'])) {
                $condition = [
                    'ConditionType' => $product['condition']
                ];
                // $feed['Message'][$key]['Product']['Condition'] = $condition;
            }
            if (!empty($product['brand'])) {
                $feed['Message'][$key]['DescriptionData']['Brand'] = $product['Brand'];
            }
            if (!empty($product['manufacturer'])) {
                $feed['Message'][$key]['Product']['DescriptionData']['Manufacturer'] = $product['Manufacturer'];
            }
            if (!empty($product['mfr_part_number'])) {
                $feed['Message'][$key]['Product']['DescriptionData']['MfrPartNumber'] = $product['MfrPartNumber'];
            }
            if (!empty($product['BulletPoint'])) {
                $feed['Message'][$key]['Product']['DescriptionData']['BulletPoint'] = $product['BulletPoint'];
            }
        }
        return $this->submitFeed('_POST_PRODUCT_DATA_', $feed);
    }
    /**
     * Post to create or update a product (_POST_FLAT_FILE_LISTINGS_DATA_)
     * @param  object $MWSProduct or array of MWSProduct objects
     * @return array
     */
    public function postProductOld($MWSProduct)
    {
        if (!is_array($MWSProduct)) {
            $MWSProduct = [$MWSProduct];
        }
        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->setDelimiter("\t");
        $csv->setInputEncoding('iso-8859-1');
        $csv->insertOne(['TemplateType=Offer', 'Version=2014.0703']);
        $header = ['sku', 'price', 'quantity', 'product-id',
            'product-id-type', 'condition-type', 'condition-note',
            'ASIN-hint', 'title', 'product-tax-code', 'operation-type',
            'sale-price', 'sale-start-date', 'sale-end-date', 'leadtime-to-ship',
            'launch-date', 'is-giftwrap-available', 'is-gift-message-available',
            'fulfillment-center-id', 'main-offer-image', 'offer-image1',
            'offer-image2', 'offer-image3', 'offer-image4', 'offer-image5'
        ];
        $csv->insertOne($header);
        $csv->insertOne($header);
        foreach ($MWSProduct as $product) {
            $csv->insertOne(
                array_values($product->makeToArray())
            );
        }
        return $this->submitFeed('_POST_FLAT_FILE_LISTINGS_DATA_', $csv);
    }
    /**
     * Returns the feed processing report and the Content-MD5 header.
     * @param string $FeedSubmissionId
     * @return array
     */
    public function getFeedSubmissionResult($FeedSubmissionId)
    {
        $result = $this->request('GetFeedSubmissionResult', [
            'FeedSubmissionId' => $FeedSubmissionId
        ]);
        if (isset($result['Message']['ProcessingReport'])) {
            return $result['Message']['ProcessingReport'];
        } else {
            return $result;
        }
    }
    /**
     * Uploads a feed for processing by Amazon MWS.
     * @param string $FeedType (http://docs.developer.amazonservices.com/en_US/feeds/Feeds_FeedType.html)
     * @param mixed $feedContent Array will be converted to xml using https://github.com/spatie/array-to-xml. Strings will not be modified.
     * @param boolean $debug Return the generated xml and don't send it to amazon
     * @return array
     */
    public function submitFeed($FeedType, $feedContent, $debug = false)
    {
        if (is_array($feedContent)) {
            $feedContent = $this->arrayToXml(
                array_merge([
                    'Header' => [
                        'DocumentVersion' => 1.01,
                        'MerchantIdentifier' => $this->config['Seller_Id']
                    ]
                ], $feedContent)
            );
        }
        if ($debug === true) {
            return $feedContent;
        } elseif ($this->debugNextFeed == true) {
            $this->debugNextFeed = false;
            return $feedContent;
        }
        $query = [
            'FeedType' => $FeedType,
            'PurgeAndReplace' => 'false',
            'Merchant' => $this->config['Seller_Id'],
            'MarketplaceId.Id.1' => false,
            'SellerId' => false,
        ];
        $query['MarketplaceIdList.Id.1'] = $this->config['Marketplace_Id'];
        $response = $this->request(
            'SubmitFeed',
            $query,
            $feedContent
        );
        return $response['SubmitFeedResult']['FeedSubmissionInfo'];
    }
    /**
     * Convert an array to xml
     * @param $array array to convert
     * @param $customRoot [$customRoot = 'AmazonEnvelope']
     * @return sting
     */
    private function arrayToXml(array $array, $customRoot = 'AmazonEnvelope')
    {
        return ArrayToXml::convert($array, $customRoot);
    }
    /**
     * Convert an xml string to an array
     * @param string $xmlstring
     * @return array
     */
    private function xmlToArray($xmlstring)
    {
        ;
        return json_decode(json_encode(simplexml_load_string($xmlstring)), true);
    }
    /**
     * Creates a report request and submits the request to Amazon MWS.
     * @param string $report (http://docs.developer.amazonservices.com/en_US/reports/Reports_ReportType.html)
     * @param DateTime [$StartDate = null]
     * @param EndDate [$EndDate = null]
     * @return string ReportRequestId
     */
    public function requestReport($report, $StartDate = null, $EndDate = null)
    {
        $query = [
            'MarketplaceIdList.Id.1' => $this->config['Marketplace_Id'],
            'ReportType' => $report
        ];
        if (!empty($StartDate)) {
            if (!is_a($StartDate, 'DateTime')) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase('StartDate should be a DateTime object')
                );
            } else {
                $query['StartDate'] = gmdate(self::DATE_FORMAT, $StartDate->getTimestamp());
            }
        }
        if (!empty($EndDate)) {
            if (!is_a($EndDate, 'DateTime')) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase('EndDate should be a DateTime object')
                );
            } else {
                $query['EndDate'] = gmdate(self::DATE_FORMAT, $EndDate->getTimestamp());
            }
        }
        $result = $this->request(
            'RequestReport',
            $query
        );
        if (isset($result['RequestReportResult']['ReportRequestInfo']['ReportRequestId'])) {
            return $result['RequestReportResult']['ReportRequestInfo']['ReportRequestId'];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Error trying to request report')
            );
        }
    }
    /**
     * Get a report's content
     * @param string $ReportId
     * @return array on succes
     */
    public function getReport($ReportId)
    {
        $status = $this->getReportRequestStatus($ReportId);
        if ($status !== false && $status['ReportProcessingStatus'] === '_DONE_NO_DATA_') {
            return [];
        } elseif ($status !== false && $status['ReportProcessingStatus'] === '_DONE_') {
            $result = $this->request('GetReport', [
                'ReportId' => $status['GeneratedReportId']
            ]);
            if (is_string($result)) {
                $csv = Reader::createFromString($result);
                $csv->setDelimiter("\t");
                $headers = $csv->fetchOne();
                $result = [];
                foreach ($csv->setOffset(1)->fetchAll() as $row) {
                    $result[] = array_combine($headers, $row);
                }
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Get a report's processing status
     * @param string  $ReportId
     * @return array if the report is found
     */
    public function getReportRequestStatus($ReportId)
    {
        $result = $this->request('GetReportRequestList', [
            'ReportRequestIdList.Id.1' => $ReportId
        ]);
        if (isset($result['GetReportRequestListResult']['ReportRequestInfo'])) {
            return $result['GetReportRequestListResult']['ReportRequestInfo'];
        }
        return false;
    }
    
    /**
     * Get a list's inventory for Amazon's fulfillment
     *
     * @param array $skuArray
     *
     * @return array
     * @throws Exception
     */
    public function listInventorySupply($skuArray = [])
    {
        if (count($skuArray) > 50) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Maximum amount of SKU\'s for this call is 50')
            );
        }
    
        $counter = 1;
        $query = [
            'MarketplaceId' => $this->config['Marketplace_Id']
        ];
    
        foreach ($skuArray as $key) {
            $query['SellerSkus.member.' . $counter] = $key;
            $counter++;
        }
    
        $response = $this->request(
            'ListInventorySupply',
            $query
        );
    
        $result = [];
        if (isset($response['ListInventorySupplyResult']['InventorySupplyList']['member'])) {
            foreach ($response['ListInventorySupplyResult']['InventorySupplyList']['member'] as $index => $ListInventorySupplyResult) {
                $result[$index] = $ListInventorySupplyResult;
            }
        }
        
        return $result;
    }

    public function getFulfillmentPreview($address, $items)
    {
        $counter = 1;
        $query = [
            'MarketplaceId' => $this->config['Marketplace_Id']
        ];
    
        foreach ($address as $adKey => $adsVal) {
            $query['Address.'.$adKey] = $adsVal;
        }
        foreach ($items as $key => $value) {
            $query['Items.member.' . $counter.'.'.$key] = $key;
            $counter++;
        }
    
        $response = $this->request(
            'GetFulfillmentPreview',
            $query
        );
    
        $result = [];
        if (isset($response['GetFulfillmentPreviewResult']['FulfillmentPreviews']['member'])) {
            $result = $response['GetFulfillmentPreviewResult']['FulfillmentPreviews']['member'];
        }
        
        return $result;
    }

    /**
     * create fulfillment order
     */
    public function createFulfillmentOrder($request, $address, $items, $notificationEmail = '')
    {
        $counter = 1;
        $query = [
            'MarketplaceId' => $this->config['Marketplace_Id']
        ];
    
        foreach ($request as $requestKey => $requestVal) {
            $query[$requestKey] = $requestVal;
        }
        foreach ($address as $addresskey => $addressVal) {
            $query['DestinationAddress.'.$addresskey] = $addressVal;
        }
    
        foreach ($items as $itemKey => $item) {
            foreach ($item as $key => $value) {
                $query['Items.member.' . $counter.'.'.$key] = $value;
            }
            $counter++;
        }
    
        // set notification email in query
        if (!empty($notificationEmail)) {
            $query['NotificationEmailList.member.1'] = $notificationEmail;
        }

        $response = $this->request(
            'CreateFulfillmentOrder',
            $query
        );
    
        return $response;
    }

    /**
     * can fulfillment order
     */
    public function cancelFulfillmentOrder($sellerFulfillmentOrderId)
    {
        $query = [
            'MarketplaceId' => $this->config['Marketplace_Id'],
            'SellerFulfillmentOrderId'  => $sellerFulfillmentOrderId
        ];
        $response = $this->request(
            'CancelFulfillmentOrder',
            $query
        );
        return $response;
    }

    /**
     * get response of seller fulfillment order
     */
    public function getFulfillmentOrder($sellerFulfillmentOrderId)
    {
        $query = [
            'MarketplaceId' => $this->config['Marketplace_Id'],
            'SellerFulfillmentOrderId'  => $sellerFulfillmentOrderId
        ];
        $response = $this->request(
            'GetFulfillmentOrder',
            $query
        );
        return $response;
    }
    
    /**
     * register destination
     * @param string $url
     */
    public function registerDestination($url)
    {
        $query = [
            'MarketplaceId' => $this->config['Marketplace_Id']
        ];
        $query['Destination.DeliveryChannel'] = 'SQS';
        $query['Destination.AttributeList.member.1.Key'] = 'sqsQueueUrl';
        $query['Destination.AttributeList.member.1.Value'] = $url;

        $response = $this->request(
            'RegisterDestination',
            $query
        );
        return $response;
    }

    /**
     * create destination
     * @param string $url
     * @param string $notificationType
     */
    public function createSubscription($url, $notificationType)
    {
        $query = [
            'MarketplaceId' => $this->config['Marketplace_Id']
        ];
        $query['Subscription.Destination.DeliveryChannel'] = 'SQS';
        $query['Subscription.Destination.AttributeList.member.1.Key'] = 'sqsQueueUrl';
        $query['Subscription.Destination.AttributeList.member.1.Value'] = $url;
        $query['Subscription.IsEnabled'] = 'true';
        $query['Subscription.NotificationType'] = $notificationType;

        $response = $this->request(
            'CreateSubscription',
            $query
        );
        return $response;
    }

    /**
     * create destination
     * @param string $url
     * @param string $notificationType
     */
    public function listSubscriptions()
    {
        $query = [
            'MarketplaceId' => $this->config['Marketplace_Id']
        ];

        $response = $this->request(
            'ListSubscriptions',
            $query
        );
        return $response;
    }

    /**
     * send test notification
     */
    public function sendTestNotificationToDestination($url)
    {
        $query = [
            'MarketplaceId' => $this->config['Marketplace_Id']
        ];
        $query['Destination.DeliveryChannel'] = 'SQS';
        $query['Destination.AttributeList.member.1.Key'] = 'sqsQueueUrl';
        $query['Destination.AttributeList.member.1.Value'] = $url;

        $response = $this->request(
            'SendTestNotificationToDestination',
            $query
        );
        return $response;
    }

    /**
     * Request MWS
     */
    private function request($endPoint, array $query = [], $body = null, $raw = false)
    {

        $endPoint = MwsEndPoint::get($endPoint);
        $merge = [
            'Timestamp' => gmdate(self::DATE_FORMAT, time()),
            'AWSAccessKeyId' => $this->config['Access_Key_ID'],
            'Action' => $endPoint['action'],
            //'MarketplaceId.Id.1' => $this->config['Marketplace_Id'],
            'SellerId' => $this->config['Seller_Id'],
            'SignatureMethod' => self::SIGNATURE_METHOD,
            'SignatureVersion' => self::SIGNATURE_VERSION,
            'Version' => $endPoint['date'],
        ];
        $query = array_merge($merge, $query);
        if (!isset($query['MarketplaceId.Id.1'])) {
            $query['MarketplaceId.Id.1'] = $this->config['Marketplace_Id'];
        }
        if (!empty($this->config['MWSAuthToken'])) {
            $query['MWSAuthToken'] = $this->config['MWSAuthToken'];
        }
        if (isset($query['MarketplaceId'])) {
            unset($query['MarketplaceId.Id.1']);
        }
        if (isset($query['MarketplaceIdList.Id.1'])) {
            unset($query['MarketplaceId.Id.1']);
        }
        try {
            $headers = [
                'Accept' => 'application/xml',
                'x-amazon-user-agent' => $this->config['Application_Name'] . '/' . $this->config['Application_Version']
            ];
            if ($endPoint['action'] === 'SubmitFeed') {
                $headers['Content-MD5'] = base64_encode(md5($body, true));
                $headers['Content-Type'] = 'text/xml; charset=iso-8859-1';
                $headers['Host'] = $this->config['Region_Host'];
                unset(
                    $query['MarketplaceId.Id.1'],
                    $query['SellerId']
                );
            }
            $requestOptions = [
                'headers' => $headers,
                'body' => $body
            ];
            ksort($query);
            $query['Signature'] = base64_encode(
                hash_hmac(
                    'sha256',
                    $endPoint['method']
                    . "\n"
                    . $this->config['Region_Host']
                    . "\n"
                    . $endPoint['path']
                    . "\n"
                    . http_build_query($query, null, '&', PHP_QUERY_RFC3986),
                    $this->config['Secret_Access_Key'],
                    true
                )
            );
            $requestOptions['query'] = $query;
            
            if ($this->client === null) {
                $this->client = new Client();
            }
            $response = $this->client->request(
                $endPoint['method'],
                $this->config['Region_Url'] . $endPoint['path'],
                $requestOptions
            );
        
            $body = (string) $response->getBody();
            if ($raw) {
                return $body;
            } elseif (strpos(strtolower($response->getHeader('Content-Type')[0]), 'xml') !== false) {
                return $this->xmlToArray($body);
            } else {
                return $body;
            }
        } catch (BadResponseException $e) {
            if ($e->hasResponse()) {
                $message = $e->getResponse();
                $message = $message->getBody();
                if (strpos($message, '<ErrorResponse') !== false) {
                    return $this->xmlToArray($message);
                }
            } else {
                return [ 'error' =>
                            ['message' =>'An error occured']
                        ];
            }
        }
    }
    
    public function setClient(Client $client)
    {
        $this->client = $client;
    }
}
