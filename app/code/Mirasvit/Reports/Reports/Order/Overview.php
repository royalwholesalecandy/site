<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-reports
 * @version   1.3.31
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Reports\Reports\Order;

use Magento\Framework\Stdlib\DateTime;
use Mirasvit\Report\Model\AbstractReport;
use Mirasvit\Report\Model\Context;
use Mirasvit\ReportApi\Api\RequestInterface;
use Mirasvit\ReportApi\Processor\ResponseItem;
use Mirasvit\Reports\Model\Config;

class Overview extends AbstractReport
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        Config $config,
        Context $context
    ) {
        $this->config = $config;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Sales Overview');
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('sales_order');

        $this->setPrimaryFilters([
            'sales_order|created_at',
            'sales_order|store_id',
        ]);

        $this->setColumns([
            'sales_order|entity_id__cnt',
            'sales_order|total_qty_ordered__sum',
            'sales_order|discount_amount__sum',
            'sales_order|shipping_amount__sum',
            'sales_order|tax_amount__sum',
            'sales_order|total_refunded__sum',
            'sales_order|gross_margin__avg',
            'sales_order|grand_total__sum',
        ]);

        $this->setDimensions([
            'sales_order|created_at__day',
        ]);

        $this->setPrimaryDimensions([
            'sales_order|created_at__day',
            'sales_order|created_at__week',
            'sales_order|created_at__month',
            'sales_order|created_at__quarter',
            'sales_order|created_at__year',
        ]);

        //        $this->addColumns($this->context->getProvider()->getComplexColumns('sales_order'));
        //        $this->addColumns($this->context->getProvider()->getComplexColumns('sales_order_item'));
        //        $this->addColumns($this->context->getProvider()->getComplexColumns('sales_order_payment'));

        $this->getChartConfig()
            ->setType('column')
            ->setDefaultColumns([
                'sales_order|grand_total__sum',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getActions(ResponseItem $item, RequestInterface $request)
    {
        return false;
        $dimension = $request->getDimensions()[0];

        if (!in_array($dimension, [
            'sales_order|created_at__day',
            'sales_order|created_at__week',
            'sales_order|created_at__month'])
        ) {
            return false;
        }

        return [
            [
                'label' => __('View Orders'),
                'href'  => $this->getRangeUrl(
                    $dimension,
                    $item->getData($dimension),
                    'order_plain'
                ),
            ],
            [
                'label' => __('View Products'),
                'href'  => $this->getRangeUrl(
                    $dimension,
                    $item->getData($dimension),
                    'catalog_product'
                ),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getRangeUrl($dimension, $fromDate, $report = 'Order_Plain')
    {
        $fromDate = (new DateTime())->strToTime($fromDate);
        $toDate   = $fromDate;

        switch ($dimension) {
            case 'sales_order|created_at__day':
                $toDate += 24 * 60 * 60;
                break;

            case 'sales_order|created_at__week':
                $toDate += 7 * 24 * 60 * 60;
                break;

            case 'sales_order|created_at__month':
                $toDate += 30 * 24 * 60 * 60;
                break;

            case 'sales_order|created_at__year':
                $toDate += 365 * 24 * 60 * 60;
                break;

            default:
                return false;
        }

        return $this->context->urlManager->getUrl(
            'reports/report/view',
            [
                'report' => $report,
                '_query' => [
                    'filters' => [
                        'sales_order|created_at' => [
                            'from' => (new \Zend_Date($fromDate))->get(DateTime::DATETIME_INTERNAL_FORMAT),
                            'to'   => (new \Zend_Date($toDate))->get(DateTime::DATETIME_INTERNAL_FORMAT),
                        ],
                    ],
                ],
            ]
        );
    }
}
