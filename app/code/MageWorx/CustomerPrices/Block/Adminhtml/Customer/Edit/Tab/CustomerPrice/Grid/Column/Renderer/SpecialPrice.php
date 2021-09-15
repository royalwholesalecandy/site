<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Block\Adminhtml\Customer\Edit\Tab\CustomerPrice\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Framework\Locale\CurrencyInterface;

class SpecialPrice extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    const PRECISION = 2;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $modelCurrency;

    /**
     * SpecialPrice constructor.
     *
     * @param Context $context
     * @param CurrencyInterface $localeCurrency
     * @param \Magento\Directory\Model\Currency $modelCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        CurrencyInterface $localeCurrency,
        \Magento\Directory\Model\Currency $modelCurrency,
        array $data = []
    ) {
        $this->localeCurrency = $localeCurrency;
        $this->modelCurrency  = $modelCurrency;
        $this->context        = $context;

        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';

        $specialPrice = $row->getSpecialPrice();
        if ($specialPrice > 0) {
            $baseCurrencyCode = (string)$this->context->getScopeConfig()->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $symbol           = $this->localeCurrency->getCurrency($baseCurrencyCode)->getSymbol();
            $formattedPrice   = $this->modelCurrency->format(
                $specialPrice,
                ['symbol' => $symbol, 'precision' => self::PRECISION],
                false,
                false
            );
            $html             = $formattedPrice;
        }

        return $html;
    }
}
