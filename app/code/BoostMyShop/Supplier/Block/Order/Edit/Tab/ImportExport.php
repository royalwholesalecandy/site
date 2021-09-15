<?php
namespace BoostMyShop\Supplier\Block\Order\Edit\Tab;

class ImportExport extends \Magento\Backend\Block\Template
{
    protected $_template = 'Order/Edit/Tab/ImportExport.phtml';

    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context, 
        \Magento\Framework\Registry $registry, array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $registry;
    }

    
    public function getPoExportUrl()
    {
        return $this->getUrl('supplier/order/exportPo', ['_current' => true]);
    }
}