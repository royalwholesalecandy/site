<?php

namespace BoostMyShop\Margin\Block\Widget\Grid\Column\Renderer\Cogs;

use Magento\Framework\DataObject;

class Cost extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_marginHelper;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \BoostMyShop\Margin\Model\Margin $marginHelper,
                                array $data = [])
    {
        parent::__construct($context, $data);
        $this->_marginHelper = $marginHelper;
    }

    public function render(DataObject $row)
    {
        $this->_marginHelper->init($row->getorder_id());
        return $this->_marginHelper->getOrderItemCost($row);
    }


}