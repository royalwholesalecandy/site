<?php

namespace BoostMyShop\AdvancedStock\Block\LowStock\Renderer;

use Magento\Framework\DataObject;

class IdealStockLevel extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_config;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_config = $config;
    }

    public function render(DataObject $row)
    {
        $html = '';
        $checkboxName = 'lowstock['.$row->getwi_id().'][wi_use_config_ideal_stock_level]';
        $textboxName = 'lowstock['.$row->getwi_id().'][wi_ideal_stock_level]';
        $textboxId = 'lowstock_'.$row->getwi_id().'_wi_ideal_stock_level';

        $onchange = 'lowStock.logChange(\''.$row->getwi_id().'\', \'wi_use_config_ideal_stock_level\', (this.checked ? 1 : 0)); lowStock.checkboxToggle(this, \''.$textboxId.'\')';
        $html .= '<input type="checkbox" onchange="'.$onchange.'" name="'.$checkboxName.'" id="'.$checkboxName.'" value="1" '.($row->getwi_use_config_ideal_stock_level() ? ' checked="checked" ' : '').'>';
        $html .= __('Use default').' ('.$this->_config->getSetting('stock_level/default_ideal').')';

        $html .= '<br><input type="text" onchange="lowStock.logChange(\''.$row->getwi_id().'\', \'wi_ideal_stock_level\', this.value);" name="'.$textboxName.'" id="'.$textboxId.'" value="'.$row->getwi_ideal_stock_level().'" size="3" style="'.($row->getwi_use_config_ideal_stock_level() ? 'display: none;' : '').'">';

        return $html;
    }

    public function renderExport(DataObject $row)
    {
        if ($row->getwi_use_config_ideal_stock_level())
            return $this->_config->getSetting('stock_level/default_ideal');
        else
            return $row->getwi_ideal_stock_level();
    }
}