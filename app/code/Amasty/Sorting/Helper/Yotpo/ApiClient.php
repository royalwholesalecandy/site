<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Helper\Yotpo;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;

class ApiClient extends AbstractHelper
{
    const ALL_PRODUCTS_PATH = 'v1/apps/YOUR_APP_KEY/products';

    const REVIEWS_PATH = 'v1/apps/YOUR_APP_KEY/reviews';

    const APP_KEY = 'YOUR_APP_KEY';

    const USER_TOKEN = 'USER_TOKEN';

    const PRODUCTS_COUNT = 100;

    const REVIEWS_COUNT = 30;

    const LAST_TIME_UPDATED = 'amsorting/rating_summary/last_time';

    /**
     * @var \Yotpo\Yotpo\Helper\ApiClient|null
     */
    private $yotpoClient = null;

    /**
     * @var \Yotpo\Yotpo\Block\Config|null
     */
    private $yotpoConfig = null;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $accessTokens = [];

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    public function __construct(
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        WriterInterface $configWriter,
        TimezoneInterface $timezone,
        ReinitableConfigInterface $reinitableConfig,
        Context $context
    ) {
        parent::__construct($context);
        if ($this->_moduleManager->isEnabled('Yotpo_Yotpo')) {
            $this->yotpoClient = $objectManager->get('Yotpo\Yotpo\Helper\ApiClient');
            $this->yotpoConfig = $objectManager->get('Yotpo\Yotpo\Block\Config');
        }
        $this->storeManager = $storeManager;
        $this->configWriter = $configWriter;
        $this->timezone = $timezone;
        $this->reinitableConfig = $reinitableConfig;
    }

    /**
     * @param $storeId
     * @return string|null
     */
    public function getAccessToken($storeId)
    {
        if ($this->yotpoClient && !isset($this->accessTokens[$storeId])) {
            $this->accessTokens[$storeId] = $this->yotpoClient->oauthAuthentication($storeId);
        }

        return isset($this->accessTokens[$storeId]) ? $this->accessTokens[$storeId] : null;
    }

    /**
     * @return array
     */
    public function collectReviews()
    {
        $reviews = [];
        $firstLoad = true;
        if ($this->yotpoClient) {
            foreach ($this->storeManager->getStores() as $store) {
                $storeId = $store->getId();
                $lastTime = $this->scopeConfig->getValue(self::LAST_TIME_UPDATED, ScopeInterface::SCOPE_STORE, $storeId);
                if ($lastTime) {
                    $firstLoad = false;
                    $this->fetchNewReviews($reviews, $lastTime, $storeId);
                    $this->updateLastTime($storeId, $lastTime);
                } else {
                    $this->getProductsInfo($reviews, $storeId);
                    $this->updateLastTime($storeId);
                }
            }
        }

        return [$firstLoad, $reviews];
    }

    /**
     * @param string $path
     * @return mixed
     */
    private function sendApiRequest($path)
    {
        $ch = curl_init($path);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        curl_close($ch);

        $result = json_decode($result, true);

        return $result;
    }

    /**
     * @param string $path
     * @param $storeId
     * @param array $getParams
     * @return string
     */
    private function updatePath($path, $storeId, $getParams = [])
    {
        $path = str_replace(
            self::APP_KEY,
            $this->yotpoConfig->getAppKey($storeId),
            $path
        );
        $getParams['utoken'] = $this->getAccessToken($storeId);
        $path .= '?';
        $path .= http_build_query($getParams);

        return \Yotpo\Yotpo\Helper\ApiClient::YOTPO_SECURED_API_URL . '/' . $path;
    }

    /**
     * @param array $reviews
     * @param int $storeId
     */
    public function getProductsInfo(&$reviews, $storeId)
    {
        $page = 1;
        while (true) {
            $productsInfo = $this->sendApiRequest($this->updatePath(
                self::ALL_PRODUCTS_PATH,
                $storeId,
                ['count' => self::PRODUCTS_COUNT, 'page' => $page]
            ));
            if (isset($productsInfo['products']) && !empty($productsInfo['products'])) {
                foreach ($productsInfo['products'] as $product) {
                    $reviews[] = [
                        'product_id' => $product['external_product_id'],
                        'rating_summary' => $product['average_score'],
                        'store_id' => $storeId,
                        'total_reviews' => $product['total_reviews']
                    ];
                }
                $page++;
            } else {
                break;
            }
        }
    }

    /**
     * @param array $reviews
     * @param string $since
     * @param int $storeId
     */
    public function fetchNewReviews(&$reviews, &$since, $storeId)
    {
        $page = 1;
        while (true) {
            $productReviews = $this->sendApiRequest($this->updatePath(
                self::REVIEWS_PATH,
                $storeId,
                ['count' => self::REVIEWS_COUNT, 'page' => $page, 'since_date' => $since]
            ));
            if (isset($productReviews['reviews']) && !empty($productReviews['reviews'])) {
                foreach ($productReviews['reviews'] as $review) {
                    if (!isset($review['sku']) || !isset($review['score'])) {
                        continue;
                    }
                    if (isset($reviews[$review['sku']])) {
                        $reviews[$review['sku']]['score'] += $review['score'];
                        $reviews[$review['sku']]['count']++;
                    } else {
                        $reviews[$review['sku']] = [
                            'score' => $review['score'],
                            'count' => 1,
                            'store_id' => $storeId
                        ];
                    }
                }
                $since = $review['created_at'];
                $page++;
            } else {
                break;
            }
        }
    }

    /**
     * @param int $storeId
     * @param null|string $lastTime
     */
    private function updateLastTime($storeId, $lastTime = null)
    {
        if (!$lastTime) {
            $lastTime = $this->timezone->date()->format('Y-m-d\TH:i:s.m\Z');
        }
        $this->configWriter->save(self::LAST_TIME_UPDATED, $lastTime, ScopeInterface::SCOPE_STORES, $storeId);
        $this->reinitableConfig->reinit();
    }
}
