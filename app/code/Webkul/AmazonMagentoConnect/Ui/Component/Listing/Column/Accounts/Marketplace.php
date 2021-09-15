<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Ui\Component\Listing\Column\Accounts;

class Marketplace implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(
        \Webkul\AmazonMagentoConnect\Model\Config\Source\AmazonMarketplace $amazonMarketplace
    ) {
        $this->amazonMarketplace = $amazonMarketplace;
    }
    /**
     * Options getter.
     *
     * @return array
     */

    public function toOptionArray()
    {
        $amzMp = [];
        $marketplace = $this->amazonMarketplace->toArray();
        foreach ($marketplace as $marketplaceCode => $label) {
            $amzMp[] = [
                'value' => $marketplaceCode,
                'label' => __($label)
            ];
        }
        return $amzMp;
    }
}
