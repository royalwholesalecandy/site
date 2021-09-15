<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Pricing\Render;

use Magento\Catalog\Pricing\Render\FinalPriceBox as CatalogFinalPriceBox;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\Amount\AmountFactory;

/**
 * Class FinalPriceBox
 * @package Mageplaza\DailyDeal\Pricing\Render
 */
class FinalPriceBox extends CatalogFinalPriceBox
{
    /**
     * @var AmountFactory
     */
    protected $amountFactory;

    /**
     * Amount Factory
     *
     * @return AmountFactory
     */
    public function getAmountFactory()
    {
        if (is_null($this->amountFactory)) {
            $this->amountFactory = ObjectManager::getInstance()->get(AmountFactory::class);
        }

        return $this->amountFactory;
    }

    /**
     * @param $price
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getDealPriceAmount($price)
    {
        return $this->getAmountFactory()->create($price);
    }
}
