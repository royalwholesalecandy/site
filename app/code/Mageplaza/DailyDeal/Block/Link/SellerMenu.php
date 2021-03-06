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

namespace Mageplaza\DailyDeal\Block\Link;

use Magento\Framework\View\Element\Template;
use Mageplaza\DailyDeal\Block\Pages\BestsellerDeals;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class SellerMenu
 * @package Mageplaza\DailyDeal\Block\Link
 */
class SellerMenu extends Template
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var BestsellerDeals
     */
    protected $_sellerDeals;

    /**
     * SellerMenu constructor.
     * @param Template\Context $context
     * @param HelperData $helperData
     * @param BestsellerDeals $sellerDeals
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        HelperData $helperData,
        BestsellerDeals $sellerDeals,
        array $data = []
    )
    {
        $this->_sellerDeals = $sellerDeals;
        $this->_helperData  = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * Get Page Deal Url
     *
     * @return string
     */
    public function getPageUrl()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();

        return $baseUrl . $this->getRoute() . $this->_helperData->getUrlSuffix();
    }

    /**
     * Get Page Title All Deals Page
     *
     * @return mixed
     */
    public function getPageTitle()
    {
        return $this->_sellerDeals->getPageTitle();
    }

    /**
     * Check enable All Deals Page
     *
     * @return mixed
     */
    public function isEnable()
    {
        return $this->_sellerDeals->isEnable();
    }

    /**
     * Get Route All Deals Page
     *
     * @return mixed
     */
    public function getRoute()
    {
        return $this->_sellerDeals->getRoute();
    }

    /**
     * Get position show link all deals page
     *
     * @param $position
     * @return bool
     */
    public function canShowLink($position)
    {
        return $this->_sellerDeals->canShowLink($position);
    }
}