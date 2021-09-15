<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\ResourceModel\Method;

use Magento\Framework\Exception\LocalizedException;
use Amasty\Sorting\Helper\Yotpo\ApiClient;
use Amasty\Sorting\Helper\Data;
use Amasty\Sorting\Model\Toprated as TopratedModel;
use Amasty\Sorting\Model\ResourceModel\Toprated\CollectionFactory as TopratedCollectionFactory;

/**
 * Class Toprated
 */
class Toprated extends AbstractIndexMethod
{
    const INDEX_MAIN_TABLE = 'amasty_sorting_yotpo';

    const MAIN_TABLE = 'review_entity_summary';

    /**
     * @var \Magento\Review\Model\ResourceModel\Review
     */
    protected $reviewResource;

    /**
     * @var int|null
     */
    private $entityTypeId = null;

    /**
     * @var ApiClient
     */
    private $yotpoClient;

    /**
     * @var \Amasty\Sorting\Model\Toprated
     */
    private $toprated;

    /**
     * @var TopratedCollectionFactory
     */
    private $topratedCollectionFatory;

    public function __construct(
        Context $context,
        \Magento\Review\Model\ResourceModel\Review $reviewResource,
        ApiClient $yotpoClient,
        TopratedModel $toprated,
        TopratedCollectionFactory $topratedCollectionFatory,
        $connectionName = null,
        $methodCode = '',
        $methodName = ''
    ) {
        parent::__construct($context, $connectionName, $methodCode, $methodName);
        $this->reviewResource = $reviewResource;
        $this->yotpoClient = $yotpoClient;
        $this->toprated = $toprated;
        $this->topratedCollectionFatory = $topratedCollectionFatory;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodLabel($store = null)
    {
        $storeLabel = $this->helper->getScopeValue('rating_summary/label', $store);
        if ($storeLabel) {
            return $storeLabel;
        }

        return parent::getMethodLabel($store);
    }

    /**
     * Returns Sorting method Table Column name
     * which is using for order collection
     *
     * @return string
     */
    public function getSortingColumnName()
    {
        return 'amrating_summary';
    }

    /**
     * @return string
     */
    public function getSortingFieldName()
    {
        return 'rating_summary';
    }

    /**
     * {@inheritdoc}
     * This method is also used for @see Commented
     */
    public function apply($collection, $direction)
    {
        try {
            $collection->joinField(
                $this->getSortingColumnName(),          // alias
                $this->getIndexTableName(),         // table
                $this->getSortingFieldName(),   // field
                $this->getProductColumn() . '=entity_id',     // bind
                $this->getConditions(),          // conditions
                'left'                          // join type
            );
        } catch (LocalizedException $e) {
            // A joined field with this alias is already declared.
            $this->logger->warning(
                'Failed on join table for amasty sorting method: ' . $e->getMessage(),
                ['method_code' => $this->getMethodCode()]
            );
        } catch (\Exception $e) {
            $this->logger->critical($e, ['method_code' => $this->getMethodCode()]);
        }

        return $this;
    }

    /**
     * Get Review entity type id for product
     *
     * @return bool|int|null
     */
    private function getEntityTypeId()
    {
        if ($this->entityTypeId === null) {
            $this->entityTypeId = $this->reviewResource->getEntityIdByCode(
                \Magento\Review\Model\Review::ENTITY_PRODUCT_CODE
            );
        }

        return $this->entityTypeId;
    }

    /**
     * {@inheritdoc}
     */
    public function doReindex()
    {
        if ($this->helper->isYotpoEnabled()) {
            $connection = $this->getConnection();

            list($firstLoad, $insertData) = $this->yotpoClient->collectReviews();
            if ($firstLoad) {
                $connection->insertMultiple($this->getMainTable(), $insertData);
            } else {
                $topratedCollection = $this->topratedCollectionFatory
                    ->create()
                    ->addIdFilter(array_keys($insertData));
                foreach ($topratedCollection as $toprated) {
                    $toprated->setResourceModel(Toprated::class);
                    $scoreInfo = $insertData[$toprated->getProductId()];
                    $this->calculateAverageScore($toprated, $scoreInfo);
                    unset($insertData[$toprated->getProductId()]);
                }
                $topratedCollection->save();
                if (!empty($insertData)) {
                    foreach ($insertData as $productId => $insertDatum) {
                        $yoptoInfo = [
                            'product_id' => $productId,
                            'rating_summary' => $this->getRatingSummary($insertDatum['score'], $insertDatum['count']),
                            'total_reviews' => $insertDatum['count'],
                            'store_id' => $insertDatum['store_id']
                        ];
                        $connection->insert($this->getMainTable(), $yoptoInfo);
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexTableName()
    {
        if ($this->helper->isYotpoEnabled()) {
            $table = self::INDEX_MAIN_TABLE;
        } else {
            $table = self::MAIN_TABLE;
        }

        return $table;
    }

    /**
     * @return array
     */
    private function getConditions()
    {
        $conditions = ['store_id' => $this->storeManager->getStore()->getId()];
        if (!$this->helper->isYotpoEnabled()) {
            $conditions['entity_type'] = $this->getEntityTypeId();
        }

        return $conditions;
    }

    /**
     * @return string
     */
    private function getProductColumn()
    {
        $column = $this->helper->isYotpoEnabled() ?
            'product_id' :
            'entity_pk_value';

        return $column;
    }

    /**
     * @param TopratedModel $toprated
     * @param $scoreInfo
     */
    private function calculateAverageScore($toprated, $scoreInfo)
    {
        if (isset($scoreInfo['score']) && isset($scoreInfo['count'])) {
            $score = $toprated->getRatingSummary() * $toprated->getTotalReviews() + $scoreInfo['score'];
            $total = $toprated->getTotalReviews() + $scoreInfo['count'];
            $toprated->setRatingSummary($this->getRatingSummary($score, $total));
            $toprated->setTotalReviews($total);
        }
    }

    /**
     * @param $score
     * @param $total
     * @return float
     */
    private function getRatingSummary($score, $total)
    {
        return round($score / $total, 2);
    }
}
