<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Plugin\Product\ProductList;

use Magento\Framework\App\RequestInterface;
use Mageplaza\DailyDeal\Block\Widget\AllDeal;
use Mageplaza\DailyDeal\Block\Widget\FeatureDeal;
use Mageplaza\DailyDeal\Block\Widget\NewDeal;
use Mageplaza\DailyDeal\Block\Widget\TopSellingDeal;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class ToolbarCollection
 * @package Mageplaza\DailyDeal\Plugin\Product\ProductList
 */
class ToolbarCollection
{
    /**
     * @var \Mageplaza\DailyDeal\Helper\Data
     */
    protected $_helperData;

    /**
     * @var \Mageplaza\DailyDeal\Block\Widget\AllDeal
     */
    protected $_all;

    /**
     * @var \Mageplaza\DailyDeal\Block\Widget\NewDeal
     */
    protected $_new;

    /**
     * @var \Mageplaza\DailyDeal\Block\Widget\TopSellingDeal
     */
    protected $_seller;

    /**
     * @var \Mageplaza\DailyDeal\Block\Widget\FeatureDeal
     */
    protected $_feature;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * CustomCollection constructor.
     * @param HelperData $helperData
     * @param AllDeal $all
     * @param NewDeal $new
     * @param TopSellingDeal $seller
     * @param FeatureDeal $feature
     * @param RequestInterface $request
     */
    public function __construct(
        HelperData $helperData,
        AllDeal $all,
        NewDeal $new,
        TopSellingDeal $seller,
        FeatureDeal $feature,
        RequestInterface $request
    )
    {
        $this->_helperData = $helperData;
        $this->_all        = $all;
        $this->_new        = $new;
        $this->_seller     = $seller;
        $this->_feature    = $feature;
        $this->_request    = $request;
    }

    /**
     * @param $subject
     * @param $collection
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function afterGetCollection($subject, $collection)
    {
        if (!$this->_helperData->isEnabled() || $this->_helperData->versionCompare('2.2.0')) {
            return $collection;
        }

        $fullActionName = $this->_request->getFullActionName();

        switch ($fullActionName) {
            case 'dailydeal_pages_alldeals':
                $collection = $this->_all->getProductCollection();
                break;
            case 'dailydeal_pages_newdeals':
                $collection = $this->_new->getProductCollection();
                break;
            case 'dailydeal_pages_bestsellerdeals':
                $collection = $this->_seller->getProductCollection();
                break;
            case 'dailydeal_pages_featureddeals':
                $collection = $this->_feature->getProductCollection();
                break;
            default;
                break;
        }

        return $collection;
    }
}