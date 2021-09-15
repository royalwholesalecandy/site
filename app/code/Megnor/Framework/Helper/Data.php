<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Megnor\Framework\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Store\Model\Store;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Data extends \Magento\Framework\Url\Helper\Data
{

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        TimezoneInterface $localeDate
    ) {
        $this->localeDate = $localeDate;
        parent::__construct($context);
    }

    public function checkProductIsNew($_product = null) {
        $from_date = $_product->getNewsFromDate();
        $to_date = $_product->getNewsToDate();
        $is_new = false;
        $is_new = $this->isNewProduct($from_date, $to_date);
        $today = strtotime("now");

        if ($from_date && $to_date) {
            $from_date = strtotime($from_date);
            $to_date = strtotime($to_date);
            if ($from_date <= $today && $to_date >= $today) {
                $is_new = true;
            }
        }
        elseif ($from_date && !$to_date) {
            $from_date = strtotime($from_date);
            if ($from_date <= $today) {
                $is_new = true;
            }
        }elseif (!$from_date && $to_date) {
            $to_date = strtotime($to_date);
            if ($to_date >= $today) {
                $is_new = true;
            }
        }
        return $is_new;
    }

    public function isNewProduct( $created_date, $num_days_new = 3) {
        $check = false;

        $startTimeStamp = strtotime($created_date);
        $endTimeStamp = strtotime("now");

        $timeDiff = abs($endTimeStamp - $startTimeStamp);
        $numberDays = $timeDiff/86400;// 86400 seconds in one day

        // and you might want to convert to integer
        $numberDays = intval($numberDays);
        if($numberDays <= $num_days_new) {
            $check = true;
        }

        return $check;
    }

}