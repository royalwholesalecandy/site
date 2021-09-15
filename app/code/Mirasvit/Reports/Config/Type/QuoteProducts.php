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

use Magento\Quote\Model\QuoteRepository;
use Mirasvit\ReportApi\Api\Config\AggregatorInterface;
use Mirasvit\ReportApi\Api\Config\TypeInterface;
use Mirasvit\ReportApi\Api\SchemaInterface;

class QuoteProducts implements TypeInterface
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        SchemaInterface $provider,
        $name,
        $data = []
    ) {
        $this->quoteRepository = $quoteRepository;
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
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->quoteRepository->get($actualValue);

            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($quote->getAllVisibleItems() as $item) {
                $title = __('%1 [%2] × %3', $item->getName(), $item->getSku(), intval($item->getQty()));

                $html[] = $title;
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
