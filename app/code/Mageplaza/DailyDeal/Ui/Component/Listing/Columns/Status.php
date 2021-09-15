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

namespace Mageplaza\DailyDeal\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Status
 * @package Mageplaza\DailyDeal\Ui\Component\Listing\Columns
 */
class Status extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])) {
                    $currentDate = date('d-m-Y H:i:s');
                    $dateFrom    = $item['date_from'];
                    $dateTo      = $item['date_to'];
                    $dealQty     = (int)$item['deal_qty'];
                    $saleQty     = (int)$item['sale_qty'];

                    if ($item[$this->getData('name')] == 1 && $dealQty > $saleQty) {
                        if (strtotime($dateTo) >= strtotime($currentDate) && strtotime($dateFrom) <= strtotime($currentDate)) {
                            $item[$this->getData('name')] = 'running';
                        } else if (strtotime($currentDate) < strtotime($dateFrom)) {
                            $item[$this->getData('name')] = 'upcoming';
                        } else if (strtotime($currentDate) > strtotime($dateTo)) {
                            $item[$this->getData('name')] = 'ended';
                        }
                    } else if ($item[$this->getData('name')] == 1 && $saleQty >= $dealQty) {
                        $item[$this->getData('name')] = 'ended';
                    } else {
                        $item[$this->getData('name')] = 'disable';
                    }
                }
            }
        }

        return $dataSource;
    }
}
