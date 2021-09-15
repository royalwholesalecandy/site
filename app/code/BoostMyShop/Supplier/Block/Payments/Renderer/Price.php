<?php

namespace BoostMyShop\Supplier\Block\Payments\Renderer;


class Price extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    protected $_productHelper = null;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->_priceCurrency = $priceCurrency;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $price = $row->getbsip_total();
        $currency = $row->getsup_currency();
        $formettedPrice = $this->getFormatedPrice($price, $currency);
        
        return $formettedPrice;
    }

    public function renderExport(\Magento\Framework\DataObject $row)
    {
        $price = $row->getbsip_total();
        $currency = $row->getsup_currency();
        $formettedPrice = $this->getFormatedPrice($price, $currency);

        return strip_tags($formettedPrice);
    }

    public function getFormatedPrice($price, $currency)
    {
        $precision = 2;   // for displaying price decimals 2 point
        return $this->_priceCurrency->format(
            $price,
            $includeContainer = true,
            $precision,
            $scope = null,
            $currency
        );
    }

}