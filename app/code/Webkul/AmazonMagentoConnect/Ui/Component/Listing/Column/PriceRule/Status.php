<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Ui\Component\Listing\Column\PriceRule;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter.
     *
     * @return array
     */

    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('Disabled')], ['value' => 1, 'label' => __('Enabled')]];
    }
}
