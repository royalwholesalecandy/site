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



namespace Mirasvit\Reports\Config\Type;

use Magento\Sales\Api\OrderRepositoryInterface;
use Mirasvit\ReportApi\Api\Config\AggregatorInterface;
use Mirasvit\ReportApi\Api\Config\TypeInterface;
use Mirasvit\ReportApi\Api\SchemaInterface;

class OrderProducts implements TypeInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SchemaInterface $provider,
        $name,
        $data = []
    ) {
        $this->orderRepository = $orderRepository;
    }

    public function getType()
    {
        return self::TYPE_STR;
    }

    public function getAggregators()
    {
        return [AggregatorInterface::TYPE_NONE];
    }

    public function getValueType()
    {
        return self::VALUE_TYPE_STRING;
    }

    public function getJsType()
    {
        return self::JS_TYPE_HTML;
    }

    public function getJsFilterType()
    {
        return false;
    }

    public function getFormattedValue($actualValue, AggregatorInterface $aggregator)
    {
        $html = [];

        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($actualValue);

            /** @var \Magento\Sales\Model\Order\Item $item */
            foreach ($order->getAllVisibleItems() as $item) {
                $title = __('%1 [%2] × %3', $item->getName(), $item->getSku(), intval($item->getQtyOrdered()));

                $info = [];
                $options = $item->getProductOptionByCode('attributes_info');
                if (is_array($options)) {
                    foreach ($options as $option) {
                        $info[] = $option['label'] . ': ' . $option['value'];
                    }
                }

                $html[] = $title . PHP_EOL . implode(PHP_EOL, $info);
            }

        } catch (\Exception $e) {
            return self::NA;
        }

        return implode(PHP_EOL, $html);
    }

    public function getPk($actualValue, AggregatorInterface $aggregator)
    {
        return $actualValue;
    }
}