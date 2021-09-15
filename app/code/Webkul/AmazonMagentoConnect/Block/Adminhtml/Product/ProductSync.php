<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Block\Adminhtml\Product;

class ProductSync extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Webkul\AmazonMagentoConnect\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * Retrieve order model instance
     *
     * @return string
     */
    public function getImportUrl()
    {
        $id = $this->getAccountId();
        return $this->getUrl('*/product/import', ['id' => $id]);
    }

    /**
     * Retrieve order model instance
     *
     * @return string
     */
    public function getProfilerUrl()
    {
        $id = $this->getAccountId();
        return $this->getUrl('*/product/profiler', ['id' => $id]);
    }

    /**
     * generate report id
     *
     * @return string
     */
    public function getGenerateReportUrl()
    {
        $id = $this->getAccountId();
        return $this->getUrl('*/product/generatereportid', ['id' => $id]);
    }

    /**
     * check report status
     *
     * @return string
     */
    public function getReportStatus()
    {
        $id = $this->helper->accountId =  $this->getAccountId();
        $accountInfo = $this->helper->getSellerAmzCredentials(true);
        if (!empty($accountInfo->getListingReportId())) {
            return $accountInfo->getCreatedAt();
        } else {
            return false;
        }
    }

    /**
     * check currency rate
     *
     * @return int | bool
     */
    public function checkCurrencyRate()
    {
        $id = $this->helper->accountId =  $this->getAccountId();
        $accountInfo = $this->helper->getSellerAmzCredentials(true);
        $amazonCurrency = $accountInfo->getCurrencyCode();
        $allowedCurrency = $this->helper->getAllowedCurrencies();
        if (in_array($amazonCurrency, $allowedCurrency)) {
            return $this->helper->getCurrencyRate($amazonCurrency);
        } else {
            return false;
        }
    }

    /**
     * get url of exported button
     *
     * @return string
     */
    public function getExportButtonUrl()
    {
        return $this->getUrl('*/producttoamazon/updatestatusofexportedpro', ['id' => $this->getAccountId()]);
    }

    /**
     * get record of temp table
     *
     * @return bool
     */
    public function getTempCount()
    {
        $collection = $this->helper->getTotalImported('product', $this->getAccountId(), true);
        if ($collection->getSize()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get exported pending records Count
     *
     * @return bool
     */
    public function getExportedCount()
    {
        $collection = $this->helper->getExportedProColl($this->getAccountId());
        if ($collection->getSize()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get account id
     *
     * @return int
     */
    public function getAccountId()
    {
        return $this->getRequest()->getParam('id');
    }
}
