<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class Margin extends AbstractRenderer
{

    public function render(DataObject $row)
    {

        $sellPrice = $this->getSellPrice($row);
        $cost = $row->getUnitPriceBaseWithCost();

        $margin = $sellPrice - $cost;

        if ($sellPrice > 0)
            $marginPercent = number_format($margin / $sellPrice * 100, 0, '.', '');
        else
            $marginPercent = 0;

        $html = '<table border="0">';
        $html .= '<tr><td align="left">'.__('Price').'</td><td align="right">'.$this->getCurrency()->format($sellPrice).'</td></tr>';
        $html .= '<tr><td align="left">'.__('Cost').'</td><td align="right">'.$this->getCurrency()->format($cost).'</td></tr>';
        $html .= '<tr><td align="left">'.__('Margin').'</td><td align="right"><font color="'.($margin < 0 ? 'red' : '').'">'.$this->getCurrency()->format($margin).' <i>('.$marginPercent.'%)</i></font></td></tr>';
        $html .= '</table>';

        return $html;
    }

    public function getSellPrice($row)
    {
        //todo : manage tax settings and catalog price rules ?
        $value = $row->getProduct()->getPrice();
        return $value;
    }

    public function getCurrency()
    {
        if (!$this->_currency)
            $this->_currency = $this->_currencyFactory->create()->load($this->_config->getGlobalSetting('currency/options/base'));
        return $this->_currency;
    }

}