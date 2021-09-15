<?php
/**
 *
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Model;

use MageWorx\CustomerPrices\Api\Data\CustomerPricesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices\CollectionFactory as CustomerPricesCollectionFactory;
use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices as CustomerPricesResourceModel;
use MageWorx\CustomerPrices\Model\CustomerPricesFactory;

class CustomerPricesRepository implements \MageWorx\CustomerPrices\Api\CustomerPricesRepositoryInterface
{
    /**
     * @var CustomerPricesCollectionFactory
     */
    protected $customerPricesCollectionFactory;

    /**
     * @var CustomerPricesResourceModel
     */
    protected $customerPricesResourceModel;

    /**
     * @var CustomerPrices[]
     */
    protected $instances = [];

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CustomerPricesFactory
     */
    protected $pricesModelFactory;

    /**
     * CustomerPricesRepository constructor.
     *
     * @param CustomerPricesCollectionFactory $customerPricesCollectionFactory
     * @param CustomerPricesResourceModel $customerPricesResourceModel
     * @param \MageWorx\CustomerPrices\Model\CustomerPricesFactory $pricesModelFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        CustomerPricesCollectionFactory $customerPricesCollectionFactory,
        CustomerPricesResourceModel $customerPricesResourceModel,
        CustomerPricesFactory $pricesModelFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->customerPricesCollectionFactory = $customerPricesCollectionFactory;
        $this->customerPricesResourceModel     = $customerPricesResourceModel;
        $this->searchCriteriaBuilder           = $searchCriteriaBuilder;
        $this->searchResultsFactory            = $searchResultsFactory;
        $this->pricesModelFactory              = $pricesModelFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get($customerPricesId, $editMode = false, $storeId = null, $forceReload = false)
    {
        $cacheKey = $this->getCacheKey([$editMode, $storeId]);
        if (!isset($this->instances[$customerPricesId][$cacheKey]) || $forceReload) {
            $prices = $this->pricesModelFactory->create();
            if ($editMode) {
                $prices->setData('_edit_mode', true);
            }
            $prices->load($customerPricesId);
            if (!$prices->getId()) {
                throw new NoSuchEntityException(__('Requested Record doesn\'t exist'));
            }
            $this->instances[$customerPricesId][$cacheKey] = $prices;
        }

        return $this->instances[$customerPricesId][$cacheKey];
    }

    /**
     * Get key for cache
     *
     * @param array $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }

        return md5(serialize($serializeData));
    }

    /**
     * @param CustomerPricesInterface $prices
     * @param bool $saveOptions
     * @return \MageWorx\CustomerPrices\Api\Data\CustomePricesInterface|void
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function save(\MageWorx\CustomerPrices\Api\Data\CustomerPricesInterface $prices, $saveOptions = false)
    {
        try {
            unset($this->instances[$prices->getId()]);
            $this->customerPricesResourceModel->save($prices);
        } catch (\Magento\Eav\Model\Entity\Attribute\Exception $exception) {
            throw \Magento\Framework\Exception\InputException::invalidFieldValue(
                $exception->getAttributeCode(),
                $prices->getData($exception->getAttributeCode()),
                $exception
            );
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save Prices'));
        }

        unset($this->instances[$prices->getId()]);

        if (!$prices->getId()) {
            return;
        } else {
            return $this->get($prices->getId());
        }
    }

    /**
     * @param CustomerPricesInterface $prices
     * @return bool
     * @throws CouldNotSaveException
     * @throws StateException
     */
    public function delete(\MageWorx\CustomerPrices\Api\Data\CustomerPricesInterface $prices)
    {
        $pricesId = $prices->getId();
        try {
            unset($this->instances[$pricesId]);
            $this->customerPricesResourceModel->delete($prices);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove Prices')
            );
        }
        unset($this->instances[$pricesId]);

        return true;
    }

    /**
     * Load customer prices data collection by given search criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @param bool $returnRawObjects
     * @return \Magento\Framework\Api\SearchResultsInterface|ResourceRate\Collection
     */
    public function getList(SearchCriteriaInterface $criteria, $returnRawObjects = false)
    {
        /** @var \Magento\Framework\Api\SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        /** @var \MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices\Collection $collection */
        $collection = $this->customerPricesCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter(
                    $filter->getField(),
                    [
                        $condition => $filter->getValue()
                    ]
                );
            }
        }

        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());

        $this->customerPricesResourceModel->joinEmailCustomer($collection);
        $this->customerPricesResourceModel->joinSkuProduct($collection);

        $items = $collection->load()->getItems();

        if (is_array($items)) {
            $searchResults->setItems($items);
        }

        return $searchResults;
    }

    /**
     * @param $customerPricesId
     * @return bool
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteById($customerPricesId)
    {
        $prices = $this->get($customerPricesId);

        return $this->delete($prices);
    }

    /**
     * Clean internal product cache
     *
     * @return void
     */
    public function cleanCache()
    {
        $this->instances     = null;
        $this->instancesById = null;
    }

}