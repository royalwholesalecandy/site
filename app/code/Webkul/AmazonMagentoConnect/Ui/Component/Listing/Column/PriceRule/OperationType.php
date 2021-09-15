<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Ui\Component\Listing\Column\PriceRule;

class OperationType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter.
     *
     * @return array
     */

    public function toOptionArray()
    {
        return [['value' => 'Fixed', 'label' => __('Fixed')], ['value' => 'Percent', 'label' => __('Percent')]];
    }
}
