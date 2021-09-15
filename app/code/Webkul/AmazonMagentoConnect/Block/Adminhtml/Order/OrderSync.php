<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Block\Adminhtml\Order;

use Webkul\AmazonMagentoConnect\Api\OrderMapRepositoryInterface;

class OrderSync extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        OrderMapRepositoryInterface $orderMapRepo,
        \Webkul\AmazonMagentoConnect\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderMapRepo = $orderMapRepo;
        $this->helper = $helper;
    }

    /**
     * Retrieve order import url
     *
     * @return string
     */
    public function getImportUrl()
    {
        $id = $this->getRequest()->getParam('id');
        return $this->getUrl('*/order/import', ['id' => $id]);
    }

    /**
     * Retrieve order profiler url
     *
     * @return string
     */
    public function getProfilerUrl()
    {
        $id = $this->getRequest()->getParam('id');
        return $this->getUrl('*/order/profiler', ['id' => $id]);
    }

    /**
     * generate report id
     *
     * @return string
     */
    public function getGenerateReportUrl()
    {
        $id = $this->getRequest()->getParam('id');
        return $this->getUrl('*/product/generatereportid', ['id' => $id]);
    }

    /**
     * check report status
     *
     * @return string
     */
    public function getReportStatus()
    {
        $id = $this->getRequest()->getParam('id');
        $this->helper->accountId =  $id;
        $accountInfo = $this->helper->getSellerAmzCredentials(true);
        if (!empty($accountInfo->getListingReportId())) {
            return true;
        } else {
            return false;
        }
    }


    public function getLastPurchaseDate()
    {
        $id = $this->getRequest()->getParam('id');

        $orderMapColl = $this->orderMapRepo->getCollectionByAccountId($id)->getLastItem();

        if (!empty($orderMapColl->getData())) {
            return $orderMapColl->getPurchaseDate();
        } else {
            return false;
        }
    }

    public function checkCurrencyRate()
    {
        $id = $this->getRequest()->getParam('id');
        $this->helper->accountId =  $id;
        $accountInfo = $this->helper->getSellerAmzCredentials(true);
        $amazonCurrency = $accountInfo->getCurrencyCode();
        $allowedCurrency = $this->helper->getAllowedCurrencies();
        if ($rate = $this->helper->getCurrencyRate($amazonCurrency)) {
            return $rate;
        } else {
            return false;
        }
    }

    /**
     * get record of temp table
     *
     * @return bool
     */
    public function getTempCount()
    {
        $id = $this->getRequest()->getParam('id');
        $collection = $this->helper->getTotalImported('order', $id, true);
        if ($collection->getSize()) {
            return true;
        } else {
            return false;
        }
    }
}
