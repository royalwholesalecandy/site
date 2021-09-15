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

namespace Mageplaza\DailyDeal\Plugin\Model;

use Magento\Framework\App\RequestInterface;
use Mageplaza\DailyDeal\Block\Widget\AllDeal;
use Mageplaza\DailyDeal\Block\Widget\FeatureDeal;
use Mageplaza\DailyDeal\Block\Widget\NewDeal;
use Mageplaza\DailyDeal\Block\Widget\TopSellingDeal;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class CustomLayer
 * @package Mageplaza\DailyDeal\Plugin\Model
 */
class CustomCollection
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
     * @return mixed
     */
    public function afterGetProductCollection($subject, $collection)
    {
        if (!$this->_helperData->isEnabled()) {
            return $collection;
        }

        $fullActionName = $this->_request->getFullActionName();
        switch ($fullActionName) {
            case 'dailydeal_pages_alldeals':
                $productIds = $this->_helperData->getProductIdsParent($this->_all->getProductIdsRandomDeal());
                break;
            case 'dailydeal_pages_newdeals':
                $productIds = $this->_helperData->getProductIdsParent($this->_new->getProductIds());
                break;
            case 'dailydeal_pages_bestsellerdeals':
                $productIds = $this->_helperData->getProductIdsParent($this->_seller->getProductIdsSellingDeal());
                break;
            case 'dailydeal_pages_featureddeals':
                $productIds = $this->_helperData->getProductIdsParent($this->_feature->getProductIds());
                break;
            default;
                return $collection;
                break;
        }

        $collection->addAttributeToFilter('entity_id', ["in" => $productIds]);
        if ($fullActionName == 'dailydeal_pages_newdeals') {
            $collection->getSelect()->order(new \Zend_Db_Expr('FIELD(e.entity_id,' . implode(',', $productIds) . ')'));
        }
        if ($fullActionName == 'dailydeal_pages_bestsellerdeals') {
            $collection->getSelect()->order(new \Zend_Db_Expr('FIELD(e.entity_id,' . implode(',', $productIds) . ')'));
        }

        return $collection;
    }
}