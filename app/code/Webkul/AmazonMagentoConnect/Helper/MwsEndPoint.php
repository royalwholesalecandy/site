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

class MwsEndPoint extends \Magento\Framework\Model\AbstractModel
{
    public static $endpoints = [
        'ListRecommendations' => [
            'method' => 'POST',
            'action' => 'ListRecommendations',
            'path' => '/Recommendations/2013-04-01',
            'date' => '2013-04-01'
        ],
        'ListMarketplaceParticipations' => [
            'method' => 'POST',
            'action' => 'ListMarketplaceParticipations',
            'path' => '/Sellers/2011-07-01',
            'date' => '2011-07-01'
        ],
        'GetMyPriceForSKU' => [
            'method' => 'POST',
            'action' => 'GetMyPriceForSKU',
            'path' => '/Products/2011-10-01',
            'date' => '2011-10-01'
        ],
        'GetMyPriceForASIN' => [
            'method' => 'POST',
            'action' => 'GetMyPriceForASIN',
            'path' => '/Products/2011-10-01',
            'date' => '2011-10-01'
        ],
        'GetProductCategoriesForSKU' => [
            'method' => 'POST',
            'action' => 'GetProductCategoriesForSKU',
            'path' => '/Products/2011-10-01',
            'date' => '2011-10-01'
        ],
        'GetProductCategoriesForASIN' => [
            'method' => 'POST',
            'action' => 'GetProductCategoriesForASIN',
            'path' => '/Products/2011-10-01',
            'date' => '2011-10-01'
        ],
        'GetFeedSubmissionResult' => [
            'method' => 'POST',
            'action' => 'GetFeedSubmissionResult',
            'path' => '/',
            'date' => '2009-01-01'
        ],
        'GetReportList' => [
            'method' => 'POST',
            'action' => 'GetReportList',
            'path' => '/',
            'date' => '2009-01-01'
        ],
        'GetReportRequestList' => [
            'method' => 'POST',
            'action' => 'GetReportRequestList',
            'path' => '/',
            'date' => '2009-01-01'
        ],
        'GetReport' => [
            'method' => 'POST',
            'action' => 'GetReport',
            'path' => '/',
            'date' => '2009-01-01'
        ],
        'RequestReport' => [
            'method' => 'POST',
            'action' => 'RequestReport',
            'path' => '/',
            'date' => '2009-01-01'
        ],
        'ListOrders' => [
            'method' => 'POST',
            'action' => 'ListOrders',
            'path' => '/Orders/2013-09-01',
            'date' => '2013-09-01'
        ],
        'ListOrdersByNextToken' => [
            'method' => 'POST',
            'action' => 'ListOrdersByNextToken',
            'path' => '/Orders/2013-09-01',
            'date' => '2013-09-01'
        ],
        'ListOrderItems' => [
            'method' => 'POST',
            'action' => 'ListOrderItems',
            'path' => '/Orders/2013-09-01',
            'date' => '2013-09-01'
        ],
        'GetOrder' => [
            'method' => 'POST',
            'action' => 'GetOrder',
            'path' => '/Orders/2013-09-01',
            'date' => '2013-09-01'
        ],
        'SubmitFeed' => [
            'method' => 'POST',
            'action' => 'SubmitFeed',
            'path' => '/',
            'date' => '2009-01-01'
        ],
        'GetMatchingProductForId' => [
            'method' => 'POST',
            'action' => 'GetMatchingProductForId',
            'path' => '/Products/2011-10-01',
            'date' => '2011-10-01'
        ],

        'ListMatchingProducts' => [
            'method' => 'POST',
            'action' => 'ListMatchingProducts',
            'path' => '/Products/2011-10-01',
            'date' => '2011-10-01'
        ],
        'GetMatchingProduct' => [
            'method' => 'POST',
            'action' => 'GetMatchingProduct',
            'path' => '/Products/2011-10-01',
            'date' => '2011-10-01'
        ],
        'GetCompetitivePricingForSKU' => [
            'method' => 'POST',
            'action' => 'GetCompetitivePricingForSKU',
            'path' => '/Products/2011-10-01',
            'date' => '2011-10-01'
        ],
        'GetCompetitivePricingForASIN' => [
            'method' => 'POST',
            'action' => 'GetCompetitivePricingForASIN',
            'path' => '/Products/2011-10-01',
            'date' => '2011-10-01'
        ],
        'GetLowestOfferListingsForASIN' => [
            'method' => 'POST',
            'action' => 'GetLowestOfferListingsForASIN',
            'path' => '/Products/2011-10-01',
            'date' => '2011-10-01'
        ],
        'GetLowestPricedOffersForASIN' => [
            'method' => 'POST',
            'action' => 'GetLowestPricedOffersForASIN',
            'path' => '/Products/2011-10-01',
            'date' => '2011-10-01'
        ],
        'ListInventorySupply' => [
            'method' => 'POST',
            'action' => 'ListInventorySupply',
            'path' => '/FulfillmentInventory/2010-10-01',
            'date' => '2010-10-01'
        ],
        'GetFulfillmentPreview' => [
            'method' => 'POST',
            'action' => 'GetFulfillmentPreview',
            'path' => '/FulfillmentOutboundShipment/2010-10-01',
            'date' => '2010-10-01'
        ],
        'CreateFulfillmentOrder' => [
            'method' => 'POST',
            'action' => 'CreateFulfillmentOrder',
            'path' => '/FulfillmentOutboundShipment/2010-10-01',
            'date' => '2010-10-01'
        ],
        'CancelFulfillmentOrder' => [
            'method' => 'POST',
            'action' => 'CancelFulfillmentOrder',
            'path' => '/FulfillmentOutboundShipment/2010-10-01',
            'date' => '2010-10-01'
        ],
        'GetFulfillmentOrder' => [
            'method' => 'POST',
            'action' => 'GetFulfillmentOrder',
            'path' => '/FulfillmentOutboundShipment/2010-10-01',
            'date' => '2010-10-01'
        ],
        'RegisterDestination' => [
            'method' => 'POST',
            'action' => 'RegisterDestination',
            'path' => '/Subscriptions/2013-07-01',
            'date' => '2013-07-01'
        ],
        'CreateSubscription' => [
            'method' => 'POST',
            'action' => 'CreateSubscription',
            'path' => '/Subscriptions/2013-07-01',
            'date' => '2013-07-01'
        ],
        'ListSubscriptions' => [
            'method' => 'POST',
            'action' => 'ListSubscriptions',
            'path' => '/Subscriptions/2013-07-01',
            'date' => '2013-07-01'
        ],
        'SendTestNotificationToDestination' => [
            'method' => 'POST',
            'action' => 'SendTestNotificationToDestination',
            'path' => '/Subscriptions/2013-07-01',
            'date' => '2013-07-01'
        ]
    ];

    /**
     * call mws method
     *
     * @param string $key
     * @return void
     */
    public static function get($key)
    {
        if (isset(self::$endpoints[$key])) {
            return self::$endpoints[$key];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Call to undefined endpoint ' . $key)
            );
        }
    }
}
