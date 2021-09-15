<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Directory\Helper\Data;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices as ResourceModelCustomerGroupPrices;
use MageWorx\CustomerGroupPrices\Helper\Data as Helper;

class GroupPrices extends AbstractModifier implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    protected $arrayManager;

    /**
     * @var PoolInterface
     */
    protected $pool;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $directoryHelper;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var ResourceModelCustomerGroupPrices
     */
    protected $customerGroupPricesResourceModel;

    /**
     * @var Helper
     */
    protected $helperData;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @param ArrayManager $arrayManager
     * @param StoreManagerInterface $storeManager
     * @param LocatorInterface $locator
     * @param Data $directoryHelper
     * @param ModuleManager $moduleManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupManagementInterface $groupManagement
     * @param ResourceModelCustomerGroupPrices $customerGroupPricesResourceModel
     * @param Helper $helperData
     */
    public function __construct(
        ArrayManager $arrayManager,
        StoreManagerInterface $storeManager,
        LocatorInterface $locator,
        Data $directoryHelper,
        ModuleManager $moduleManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GroupRepositoryInterface $groupRepository,
        GroupManagementInterface $groupManagement,
        ResourceModelCustomerGroupPrices $customerGroupPricesResourceModel,
        Helper $helperData
    ) {
        $this->arrayManager                     = $arrayManager;
        $this->storeManager                     = $storeManager;
        $this->locator                          = $locator;
        $this->directoryHelper                  = $directoryHelper;
        $this->moduleManager                    = $moduleManager;
        $this->searchCriteriaBuilder            = $searchCriteriaBuilder;
        $this->groupRepository                  = $groupRepository;
        $this->groupManagement                  = $groupManagement;
        $this->customerGroupPricesResourceModel = $customerGroupPricesResourceModel;
        $this->helperData                       = $helperData;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function modifyData(array $data)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->locator->getProduct();
        if (!$product || !$product->getId() || $product->getTypeId() == 'bundle') {
            return $data;
        }

        if (!empty($product->getData('mageworx_group_price'))) {
            $data = array_replace_recursive(
                $data,
                [
                    $product->getId() => [
                        static::DATA_SOURCE_DEFAULT => [
                            'mageworx_group_price' => $product->getData('mageworx_group_price'),
                        ],
                    ],
                ]
            );
        }

        if (!empty($product->getData('mageworx_special_group_price'))) {
            $data = array_replace_recursive(
                $data,
                [
                    $product->getId() => [
                        static::DATA_SOURCE_DEFAULT => [
                            'mageworx_special_group_price' => $product->getData('mageworx_special_group_price'),
                        ],
                    ],
                ]
            );
        }

        return $data;
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        if ($this->helperData->isEnabledCustomerGroupPrice()) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->locator->getProduct();

            if ($product->getTypeId() != 'bundle') {
                $this->customizeGroupPrice();
                $this->customizeSpecialGroupPrice();
            }
        }

        return $this->meta;
    }

    /**
     * Group Price
     *
     * @return $this
     */
    protected function customizeGroupPrice()
    {
        $groupPricePath = 'advanced_pricing_modal/children/advanced-pricing/children';

        if ($groupPricePath) {
            $this->meta = $this->arrayManager->merge(
                $groupPricePath,
                $this->meta,
                $this->getGroupPriceStructure()
            );
        }

        return $this;
    }

    /**
     * Special Group Price
     *
     * @return $this
     */
    protected function customizeSpecialGroupPrice()
    {
        $groupPricePath = 'advanced_pricing_modal/children/advanced-pricing/children';

        if ($groupPricePath) {
            $this->meta = $this->arrayManager->merge(
                $groupPricePath,
                $this->meta,
                $this->getSpecialGroupPriceStructure()
            );
        }

        return $this;
    }

    /**
     * Get group price dynamic rows structure
     *
     * @return array
     */
    protected function getGroupPriceStructure()
    {
        return [
            'mageworx_group_price' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType'       => 'dynamicRows',
                            'label'               => __('Group Price'),
                            'renderDefaultRecord' => false,
                            'recordTemplate'      => 'record',
                            'dataScope'           => '',
                            'dndConfig'           => [
                                'enabled' => false,
                            ],
                            'disabled'            => false,
                            'sortOrder'           => 91,
                        ],
                    ],
                ],
                'children'  => [
                    'record' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Container::NAME,
                                    'is_collection' => true,
                                    'component'     => 'Magento_Ui/js/dynamic-rows/record',
                                    'dataScope'     => '',
                                    'isTemplate'    => true,
                                ],
                            ],
                        ],
                        'children'  => [
                            'website_id'       => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'label'         => __('Website'),
                                            'dataScope'     => 'website_id',
                                            'dataType'      => Text::NAME,
                                            'formElement'   => Select::NAME,
                                            'componentType' => Field::NAME,
                                            'options'       => $this->getWebsites(),
                                            'value'         => $this->getDefaultWebsite(),
                                            'visible'       => $this->isMultiWebsites(),
                                            'disabled'      => ($this->isShowWebsiteColumn(
                                                ) && !$this->isAllowChangeWebsite()),
                                        ],
                                    ],
                                ],
                            ],
                            'cust_group'       => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'label'         => __('Customer Group'),
                                            'dataScope'     => 'cust_group',
                                            'formElement'   => Select::NAME,
                                            'componentType' => Field::NAME,
                                            'dataType'      => Text::NAME,
                                            'options'       => $this->getCustomerGroups(),
                                            'value'         => $this->getDefaultCustomerGroup(),
                                        ],
                                    ],
                                ],
                            ],
                            'group_price'      => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'label'         => __('Price'),
                                            'componentType' => Field::NAME,
                                            'formElement'   => Input::NAME,
                                            'dataType'      => Price::NAME,
                                            'enableLabel'   => true,
                                            'dataScope'     => 'group_price',
                                            'addbefore'     => $this->locator->getStore()
                                                                             ->getBaseCurrency()
                                                                             ->getCurrencySymbol(),
                                        ],
                                    ],
                                ],
                            ],
                            'group_type_price' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'label'         => __('Type Price'),
                                            'formElement'   => Select::NAME,
                                            'componentType' => Field::NAME,
                                            'dataType'      => Text::NAME,
                                            'dataScope'     => 'group_type_price',
                                            'options'       => $this->getDefaultTypePrice(),
                                        ],
                                    ],
                                ],
                            ],
                            'actionDelete'     => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'componentType' => 'actionDelete',
                                            'dataType'      => Text::NAME,
                                            'label'         => '',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];
    }

    /**
     * Get special group price dynamic rows structure
     *
     * @return array
     */
    protected function getSpecialGroupPriceStructure()
    {
        return [
            'mageworx_special_group_price' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType'       => 'dynamicRows',
                            'label'               => __('Special Group Price'),
                            'renderDefaultRecord' => false,
                            'recordTemplate'      => 'record',
                            'dataScope'           => '',
                            'dndConfig'           => [
                                'enabled' => false,
                            ],
                            'disabled'            => false,
                            'sortOrder'           => 92,
                        ],
                    ],
                ],
                'children'  => [
                    'record' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Container::NAME,
                                    'component'     => 'Magento_Ui/js/dynamic-rows/record',
                                    'dataScope'     => '',
                                    'isTemplate'    => true,
                                    'is_collection' => true,
                                ],
                            ],
                        ],
                        'children'  => [
                            'website_id'       => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'label'         => __('Website'),
                                            'dataScope'     => 'website_id',
                                            'dataType'      => Text::NAME,
                                            'formElement'   => Select::NAME,
                                            'componentType' => Field::NAME,
                                            'value'         => $this->getDefaultWebsite(),
                                            'options'       => $this->getWebsites(),
                                            'visible'       => $this->isMultiWebsites(),
                                            'disabled'      => ($this->isShowWebsiteColumn(
                                                ) && !$this->isAllowChangeWebsite()),
                                        ],
                                    ],
                                ],
                            ],
                            'cust_group'       => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'label'         => __('Customer Group'),
                                            'dataScope'     => 'cust_group',
                                            'formElement'   => Select::NAME,
                                            'componentType' => Field::NAME,
                                            'dataType'      => Text::NAME,
                                            'options'       => $this->getCustomerGroups(),
                                            'value'         => $this->getDefaultCustomerGroup(),
                                        ],
                                    ],
                                ],
                            ],
                            'group_price'      => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'label'         => __('Price'),
                                            'dataScope'     => 'group_price',
                                            'componentType' => Field::NAME,
                                            'formElement'   => Input::NAME,
                                            'dataType'      => Price::NAME,
                                            'enableLabel'   => true,
                                            'addbefore'     => $this->locator->getStore()
                                                                             ->getBaseCurrency()
                                                                             ->getCurrencySymbol(),
                                        ],
                                    ],
                                ],
                            ],
                            'group_type_price' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'label'         => __('Type Price'),
                                            'dataScope'     => 'group_type_price',
                                            'formElement'   => Select::NAME,
                                            'componentType' => Field::NAME,
                                            'dataType'      => Text::NAME,
                                            'options'       => $this->getDefaultTypePrice(),
                                        ],
                                    ],
                                ],
                            ],
                            'actionDelete'     => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'label'         => '',
                                            'dataType'      => Text::NAME,
                                            'componentType' => 'actionDelete',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];
    }

    /**
     * Get websites list
     *
     * @return array
     */
    protected function getWebsites()
    {
        $websites = [
            [
                'label' => __('All Websites') . ' [' . $this->directoryHelper->getBaseCurrencyCode() . ']',
                'value' => 0,
            ]
        ];
        $product  = $this->locator->getProduct();

        if ($product->getStoreId()) {
            /** @var \Magento\Store\Model\Website $website */
            $website = $this->storeManager->getStore($product->getStoreId())->getWebsite();

            $websites[] = [
                'label' => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                'value' => $website->getId(),
            ];
        } else {
            $websitesList      = $this->storeManager->getWebsites();
            $productWebsiteIds = $product->getWebsiteIds();
            foreach ($websitesList as $website) {
                /** @var \Magento\Store\Model\Website $website */
                if (!in_array($website->getId(), $productWebsiteIds)) {
                    continue;
                }
                $websites[] = [
                    'label' => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                    'value' => $website->getId(),
                ];
            }
        }

        return $websites;
    }

    /**
     * Retrieve default value for website
     *
     * @return int
     */
    public function getDefaultWebsite()
    {
        if ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()) {
            return $this->storeManager->getStore($this->locator->getProduct()->getStoreId())->getWebsiteId();
        }

        return 0;
    }

    /**
     * Show group prices grid website column
     *
     * @return bool
     */
    protected function isShowWebsiteColumn()
    {
        if ($this->isScopeGlobal() || $this->storeManager->isSingleStoreMode()) {
            return false;
        }

        return true;
    }

    /**
     * Check is allow change website value for combination
     *
     * @return bool
     */
    protected function isAllowChangeWebsite()
    {
        if (!$this->isShowWebsiteColumn() || $this->locator->getProduct()->getStoreId()) {
            return false;
        }

        return true;
    }

    /**
     * Show website column and switcher for group price table
     *
     * @return bool
     */
    protected function isMultiWebsites()
    {
        return !$this->storeManager->isSingleStoreMode();
    }

    /**
     * Retrieve allowed customer groups
     *
     * @return array
     */
    protected function getCustomerGroups()
    {
        if (!$this->moduleManager->isEnabled('Magento_Customer')) {
            return [];
        }
        $customerGroups = [
            [
                'label' => __('ALL GROUPS'),
                'value' => GroupInterface::CUST_GROUP_ALL,
            ]
        ];

        /** @var GroupInterface[] $groups */
        $groups = $this->groupRepository->getList($this->searchCriteriaBuilder->create());
        foreach ($groups->getItems() as $group) {
            $customerGroups[] = [
                'label' => $group->getCode(),
                'value' => $group->getId(),
            ];
        }

        return $customerGroups;
    }

    /**
     * Retrieve default value for customer group
     *
     * @return int
     */
    protected function getDefaultCustomerGroup()
    {
        return $this->groupManagement->getAllCustomersGroup()->getId();
    }

    /**
     * @return array
     */
    protected function getDefaultTypePrice()
    {
        return [
            ['value' => 0, 'label' => __('Fixed')],
            ['value' => 1, 'label' => __('Percent')]
        ];
    }

    /**
     * Check tier_price attribute scope is global
     *
     * @return bool
     */
    protected function isScopeGlobal()
    {
        return $this->locator->getProduct()
                             ->getResource();
    }
}
