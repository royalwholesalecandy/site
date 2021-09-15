<?php
namespace BoostMyShop\AdvancedStock\Block\StockTake\Edit\Tabs;

class ImportExport extends \Magento\Backend\Block\Template
{
    protected $_template = 'AdvancedStock/StockTake/Edit/Tab/ImportExport.phtml';

    protected $_coreRegistry = null;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, array $data = [])
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $registry;
    }

    /**
     * @return \BoostMyShop\AdvancedStock\Model\StockTake
     */
    public function getStockTake(){

        return $this->_coreRegistry->registry('current_stocktake');

    }

    public function getExportFileUrl()
    {
        return $this->getUrl('*/*/csvExport', ['id' => $this->getStockTake()->getId()]);
    }


}