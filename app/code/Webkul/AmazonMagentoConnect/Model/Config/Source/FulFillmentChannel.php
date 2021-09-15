<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\AmazonMagentoConnect\Model\Config\Source;

class FulFillmentChannel extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * get attribute option
     *
     * @param string $store
     * @return array
     */
    public function toOptionArray($store = null)
    {
        $fulfillmentChannnel = [
                            ['value' => 'none','label' => 'None'],
                            ['value' => 'fba','label' => 'Fulfillment by Amazon'],
                            ['value' => 'fbm','label' => 'Fulfillment by Merchant'],
                        ];

        return $fulfillmentChannnel;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $amazonFulfill = $this->toOptionArray();
        if (!$this->_options) {
            $this->_options = $amazonFulfill;
        }
        return $this->_options;
    }
}
