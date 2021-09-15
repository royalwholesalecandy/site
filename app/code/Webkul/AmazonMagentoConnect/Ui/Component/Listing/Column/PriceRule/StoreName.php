<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Ui\Component\Listing\Column\PriceRule;

class StoreName implements \Magento\Framework\Option\ArrayInterface
{

    public function __construct(
        \Webkul\AmazonMagentoConnect\Model\Accounts $accounts
    ) {
        $this->accounts = $accounts;
    }

    /**
     * Options getter.
     *
     * @return array
     */

    public function toOptionArray()
    {
        $collection = $this->accounts->getCollection();
        $amazonStores = [];
        foreach ($collection as $amzStore) {
            $amazonStores[] = [
                'value' => $amzStore->getId(),
                'label' => $amzStore->getStoreName()
            ];
        }
        return $amazonStores;
    }
}
