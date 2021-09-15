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

namespace Mageplaza\DailyDeal\Block\Widget;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Widget\Block\BlockInterface;
use Mageplaza\DailyDeal\Block\Product\View\Label;
use Mageplaza\DailyDeal\Block\Product\View\QtyItems as QtyData;
use Mageplaza\DailyDeal\Helper\Data as HelperData;
use Mageplaza\DailyDeal\Model\DealFactory;

/**
 * Class AbstractDeal
 * @package Mageplaza\DailyDeal\Block\Widget
 */
class AbstractDeal extends AbstractProduct implements BlockInterface
{
    /**
     * @var \Mageplaza\DailyDeal\Helper\Data
     */
    protected $_helperData;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Mageplaza\DailyDeal\Model\ResourceModel\DealFactory
     */
    protected $_dealFactory;

    /**
     * @var \Mageplaza\DailyDeal\Block\Product\View\QtyItems;
     */
    protected $_qtyData;

    /**
     * @var \Mageplaza\DailyDeal\Block\Product\View\Label
     */
    protected $_label;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * AbstractDeal constructor.
     * @param Context $context
     * @param HelperData $helperData
     * @param CollectionFactory $productCollectionFactory
     * @param DealFactory $dealFactory
     * @param QtyData $qtyData
     * @param Label $label
     * @param DateTime $date
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        CollectionFactory $productCollectionFactory,
        DealFactory $dealFactory,
        QtyData $qtyData,
        Label $label,
        DateTime $date,
        array $data = []
    )
    {
        $this->_helperData               = $helperData;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_dealFactory              = $dealFactory;
        $this->_qtyData                  = $qtyData;
        $this->_label                    = $label;
        $this->_date                     = $date;

        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getProductIdsDealRunning()
    {
        $productIds     = [];
        $dealCollection = $this->_dealFactory->create()->getCollection()->setOrder('sale_qty', 'DESC');

        foreach ($dealCollection as $item) {
            $productId = $item->getProductId();
            if ($this->_helperData->checkStatusDeal($productId)) {
                $productIds[] = $this->_helperData->getParentIdByChildId($productId);
            }
        }

        return $productIds;
    }

    /**
     * Get Qty of Remaining Items
     *
     * @param $productId
     * @return mixed
     */
    public function getQtyRemain($productId)
    {
        return $this->_qtyData->getQtyRemain($productId);
    }

    /**
     * Get Qty of Sold Items
     *
     * @param $productId
     * @return mixed
     */
    public function getQtySold($productId)
    {
        return $this->_qtyData->getQtySold($productId);
    }

    /**
     * Get Time countdown
     *
     * @param $productId
     * @return float|int
     */
    public function getTimeCountdown($productId)
    {
        $currentDate    = $this->_date->gmtDate('d-m-Y H:i:s');
        $dealCollection = $this->_helperData->getProductDeal($productId);
        $fromDate       = $dealCollection->getDateFrom();
        $toDate         = $dealCollection->getDateTo();

        if (strtotime($toDate) >= strtotime($currentDate) && strtotime($fromDate) <= strtotime($currentDate)) {
            return (strtotime($toDate) - strtotime($currentDate)) * 1000;
        }

        return 0;
    }

    /**
     * Get Parent configuration product by child Id
     *
     * @param $childId
     * @return \Magento\Framework\DataObject
     */
    public function getParentConfigurableProduct($childId)
    {
        $parentId   = $this->_helperData->getParentIdByChildId($childId);
        $collection = $this->_productCollectionFactory->create()->addIdFilter($parentId);
        $this->_addProductAttributesAndPrices($collection);

        return $collection->getFirstItem();
    }

    /**
     * is Enable sidebar widget
     *
     * @return mixed
     */
    public function isWidgetEnable()
    {
        return $this->_helperData->getModuleConfig('sidebar_widget/enabled');
    }

    /**
     * get JsonEncode countdown data of the deal
     *
     * @param $productId
     * @return string
     */
    public function getTimeCountdownData($productId)
    {
        $params = [
            'productId'  => $productId,
            'type'       => $this->getTypeWidget(),
            'remainTime' => $this->getTimeCountdown($productId)
        ];

        return HelperData::jsonEncode($params);
    }

    /**
     * is Show qty remain
     *
     * @return mixed
     */
    public function isWidgetShowRemainingItems()
    {
        return $this->_helperData->getModuleConfig('sidebar_widget/qty_remain');
    }

    /**
     * is Show qty sold
     *
     * @return mixed
     */
    public function isWidgetShowSoldItems()
    {
        return $this->_helperData->getModuleConfig('sidebar_widget/qty_sold');
    }

    /**
     * is Enable random deal widget on sidebar
     *
     * @return mixed
     */
    public function isEnableRandomDeal()
    {
        return $this->_helperData->getModuleConfig('sidebar_widget/random_deal/enable');
    }

    /**
     * is Enable selling deal widget on sidebar
     *
     * @return mixed
     */
    public function isEnableSellingDeal()
    {
        return $this->_helperData->getModuleConfig('sidebar_widget/selling_deal/enable');
    }

    /**
     * is Enable upcoming deal widget on sidebar
     *
     * @return mixed
     */
    public function isEnableUpcomingDeal()
    {
        return $this->_helperData->getModuleConfig('sidebar_widget/upcoming_deal/enable');
    }

    /**
     * position of selling deal widget
     *
     * @return mixed
     */
    public function getSellingShowOn()
    {
        return $this->_helperData->getModuleConfig('sidebar_widget/selling_deal/show_on');
    }

    /**
     * position of selling deal widget
     *
     * @return mixed
     */
    public function getRandomShowOn()
    {
        return $this->_helperData->getModuleConfig('sidebar_widget/random_deal/show_on');
    }

    /**
     * position of upcoming deal widget
     *
     * @return mixed
     */
    public function getUpcomingShowOn()
    {
        return $this->_helperData->getModuleConfig('sidebar_widget/upcoming_deal/show_on');
    }

    /**
     * Get Product Ids of Random Deal Product
     *
     * @return array
     */
    public function getProductIdsRandomDeal()
    {
        $dealCollection = $this->_dealFactory->create()->getCollection();

        return $this->getProductDealIds($dealCollection);
    }

    /**
     * Get condition products
     *
     * @param array $collection
     * @return array
     */
    public function getProductDealIds($collection)
    {
        $productIds = [];
        foreach ($collection as $item) {
            $productId = $item->getProductId();
            if ($this->_helperData->checkStatusDeal($productId)) {
                $productIds[] = $productId;
            }
        }

        return $productIds;
    }

    /**
     * Get Feature Deal Product Collection
     *
     * @param null $limit
     * @param array $productIds
     * @param null $type
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getDealProducts($limit = null, $productIds, $type = null)
    {
        $collection = [];

        if (!empty($productIds)) {
            $collection = $this->_productCollectionFactory->create()->addIdFilter($productIds);
            if ($limit) {
                $collection->setPageSize($limit);
            }

            if (in_array($type, ['newdeal', 'bestsell', 'updeal'])) {
                $collection->getSelect()->order(new \Zend_Db_Expr('FIELD(e.entity_id,' . implode(',', $productIds) . ')'));
            }
            $this->_addProductAttributesAndPrices($collection);
            if ($type === 'random') {
                $collection->getSelect()->orderRand();
            }
        }

        return $collection;
    }
}