<?php

namespace BoostMyShop\AdvancedStock\Block\StockDiscrepency;


class Report extends \Magento\Backend\Block\Template
{
    protected $_template = 'StockDiscrepency/Report.phtml';

    /**
     * @var \Magento\User\Model\ResourceModel\User
     */
    protected $_stockDiscrepency;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\User\Model\ResourceModel\User $resourceModel
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = [],
        \BoostMyShop\AdvancedStock\Model\StockDiscrepencies $stockDiscrepency
    ) {
        $this->_stockDiscrepency = $stockDiscrepency;
        parent::__construct($context, $data);
    }

    public function hasReport()
    {
        return $this->_stockDiscrepency->hasReport();
    }

    public function getReportData()
    {
        return $this->_stockDiscrepency->getData();
    }

    public function getColor($item)
    {
        return ($item->status == 'success' ? 'green' : 'red');
    }

    public function getUpdateReportUrl()
    {
        return $this->getUrl('*/*/update');
    }

    public function getFixReportUrl()
    {
        return $this->getUrl('*/*/update', ['fix' => 1]);
    }

}
