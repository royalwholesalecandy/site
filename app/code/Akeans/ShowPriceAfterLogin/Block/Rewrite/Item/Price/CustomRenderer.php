<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Akeans\ShowPriceAfterLogin\Block\Rewrite\Item\Price;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\CreditMemo\Item as CreditMemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Weee\Model\Tax as WeeeDisplayConfig;

/**
 * Item price render block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class CustomRenderer extends \Magento\Weee\Block\Item\Price\Renderer
{
    /**
     * Get base price for unit price excluding tax. The Weee amount will be added to unit price
     * depending on Weee display setting
     *
     * @return float
     */
    
}
