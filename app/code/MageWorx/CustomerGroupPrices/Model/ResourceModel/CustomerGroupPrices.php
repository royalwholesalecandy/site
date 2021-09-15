<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;

class CustomerGroupPrices extends AbstractDb
{
    const ALL_WEBSITES   = 0;
    const ABSOLUTE_PRICE = 0;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * CustomerGroupPrices constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param HelperData $helperData
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        HelperData $helperData
    ) {
        $this->storeManager = $storeManager;
        $this->helperData   = $helperData;
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('mageworx_customergroupprices', 'entity_id');
    }

    /**
     * @param     $productId
     * @param     $websiteId
     * @param     $price
     * @param     $priceType
     * @param     $absolutePriceType
     * @param     $groupId
     * @param int $isAllGroups
     * @param int $isManual
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveProductGroupPrice(
        $productId,
        $websiteId,
        $price,
        $priceType,
        $absolutePriceType,
        $groupId,
        $isAllGroups = 0,
        $isManual = 1
    ) {
        $connection = $this->getConnection();
        $tableName  = $this->getMainTable();
        $mathSign   = $this->helperData->getMathSign($price);

        $data = [
            'product_id'          => $productId,
            'group_id'            => $groupId,
            'is_all_groups'       => $isAllGroups,
            'website_id'          => $websiteId,
            'math_sign'           => $mathSign,
            'price'               => $price,
            'price_type'          => $priceType,
            'absolute_price_type' => $absolutePriceType,
            'assign_price'        => '1',
            'is_manual'           => $isManual
        ];
        $connection->insert($tableName, $data);
    }

    /**
     * @param $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveMultipleProductGroupPrice($data)
    {
        $connection = $this->getConnection();
        $tableName  = $this->getMainTable();
        $connection->insertMultiple($tableName, $data);
    }


    /**
     * delete data from product group price
     *
     * @param $productId
     * @param $absolutePriceType
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteProductGroupPrice($productId, $absolutePriceType)
    {
        $connection = $this->getConnection();
        $tableName  = $this->getMainTable();
        $connection->delete(
            $tableName,
            [
                'product_id' . ' = ?'          => $productId,
                'absolute_price_type' . ' = ?' => $absolutePriceType
            ]
        );
    }

    /**
     * @param array $entityIds
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteGroupPrices($entityIds)
    {
        $connection = $this->getConnection();
        $tableName  = $this->getMainTable();
        $connection->delete(
            $tableName,
            [
                'entity_id IN (?)' => $entityIds
            ]
        );
    }

    /**
     * Save data from group
     *
     * @param $groupId
     * @param $mageworxGroupPrice
     * @param $mageworxGroupPriceType
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveGroupPrice($groupId, $mageworxGroupPrice, $mageworxGroupPriceType)
    {
        $this->deleteByGroupId($groupId);

        $mathSign = $this->helperData->getMathSign($mageworxGroupPrice);

        $connection = $this->getConnection();
        $tableName  = $this->getMainTable();
        $data       = [
            'product_id'          => '',
            'group_id'            => $groupId,
            'is_all_groups'       => '',
            'website_id'          => '',
            'math_sign'           => $mathSign,
            'price'               => $mageworxGroupPrice,
            'price_type'          => $mageworxGroupPriceType,
            'absolute_price_type' => '',
            'assign_price'        => '0',
            'is_manual'           => '1'
        ];
        $connection->insert($tableName, $data);
    }

    /**
     * Delete data from group
     *
     * @param $groupId
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByGroupId($groupId)
    {
        $connection = $this->getConnection();
        $tableName  = $this->getMainTable();
        $connection->delete(
            $tableName,
            [
                'product_id' . ' = ?' => '',
                'group_id' . ' = ?'   => $groupId,
            ]
        );
    }

    /**
     * Get group prices product
     *
     * @param      $productId
     * @param null $absolutePriceType
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGroupPricesProduct($productId, $absolutePriceType = null)
    {
        if ($absolutePriceType === null) {
            $absolutePriceType = self::ABSOLUTE_PRICE;
        }

        $connection = $this->getConnection();
        $select     = $connection->select()
                                 ->from(['customergroupprices' => $this->getMainTable()])
                                 ->where('customergroupprices.product_id = ?', $productId)
                                 ->where('customergroupprices.absolute_price_type = ?', $absolutePriceType);
        $data       = $connection->fetchAll($select);

        return $data;
    }

    /**
     *
     *
     * @param $groupId
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGroupPrice($groupId)
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
                                 ->from(['customergroupprices' => $this->getMainTable()])
                                 ->where('customergroupprices.product_id = ?', 0)
                                 ->where('customergroupprices.group_id = ?', $groupId)
                                 ->where('customergroupprices.website_id = ?', self::ALL_WEBSITES)
                                 ->where('customergroupprices.absolute_price_type = ?', self::ABSOLUTE_PRICE);
        $data       = $connection->fetchRow($select);

        return $data;
    }

    /**
     * @return int|null
     */
    public function getPriceAttributeId()
    {
        $connection         = $this->getConnection();
        $attributeCodePrice = 'price';
        $select             = $connection->select()
                                         ->from(['attributeTable' => $this->_resources->getTableName('eav_attribute')])
                                         ->where('attributeTable.attribute_code = ?', $attributeCodePrice);
        $data               = $connection->fetchRow($select);

        if (!empty($data['attribute_id'])) {
            return $data['attribute_id'];
        }

        return null;
    }

    /**
     * @return int|null
     */
    public function getSpecialPriceAttributeId()
    {
        $connection                = $this->getConnection();
        $attributeCodeSpecialPrice = 'special_price';
        $select                    = $connection->select()
                                                ->from(
                                                    [
                                                        'attributeTable' =>
                                                            $this->_resources->getTableName('eav_attribute')
                                                    ]
                                                )
                                                ->where(
                                                    'attributeTable.attribute_code = ?',
                                                    $attributeCodeSpecialPrice
                                                );
        $data                      = $connection->fetchRow($select);

        if (!empty($data['attribute_id'])) {
            return $data['attribute_id'];
        }

        return null;
    }

    /**
     * Get Data from mageworx_catalog_product_entity_decimal_temp table
     *
     * @param array $ids
     * @param int $groupId
     * @return array
     * @throws \Exception
     */
    public function getDataFromDecimalTempTable(array $ids, $groupId)
    {
        $connection = $this->getConnection();
        $tableName  = $this->_resources->getTableName('mageworx_catalog_product_entity_decimal_temp');
        $rowId      = $this->helperData->getLinkField();

        $select = $connection->select()
                             ->from($tableName)
                             ->where('customer_group_id = ?', $groupId)
                             ->where($rowId . ' IN(?)', $ids);

        return $connection->fetchAll($select);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllCustomerGroupPricesData()
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
                                 ->from(['customergroupprices' => $this->getMainTable()])
                                 ->where('customergroupprices.product_id != ?', 0);
        $select->joinLeft(
            ['product' => $this->_resources->getTableName('catalog_product_entity')],
            'product.entity_id = customergroupprices.product_id',
            ['sku']
        );

        $select->joinLeft(
            ['group' => $this->_resources->getTableName('customer_group')],
            'group.customer_group_id = customergroupprices.group_id',
            ['group_name' => 'customer_group_code']
        );

        return $connection->fetchAll($select);
    }

    /**
     * Return 'group_name'=>'group_id'
     *
     * @return array
     */
    public function getAllCustomerGroupsData()
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
                                 ->from(
                                     $this->_resources->getTableName('customer_group'),
                                     ['customer_group_code', 'customer_group_id']
                                 );

        return $connection->fetchAll($select);
    }

    /**
     * @return array
     */
    public function getAllWebsiteIds()
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
                                 ->from($this->_resources->getTableName('store_website'), ['website_id']);

        return $connection->fetchAll($select);
    }

    /**
     * @return array
     */
    public function getAllCustomerGroupsName()
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
                                 ->from(
                                     $this->_resources->getTableName('customer_group'),
                                     ['customer_group_code']
                                 );

        return $connection->fetchAll($select);
    }

    /**
     * @return array
     */
    public function getAllCustomerGroupsId()
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
                                 ->from(
                                     $this->_resources->getTableName('customer_group'),
                                     ['customer_group_id']
                                 );

        return $connection->fetchAll($select);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getExportCustomerGroupPrices()
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
                                 ->from(['customergroupprices' => $this->getMainTable()])
                                 ->where('customergroupprices.is_manual = ?', 1)
                                 ->where('customergroupprices.product_id != ?', 0);
        $select->joinLeft(
            ['product' => $this->_resources->getTableName('catalog_product_entity')],
            'product.entity_id = customergroupprices.product_id',
            ['sku']
        );

        $select->joinLeft(
            ['group' => $this->_resources->getTableName('customer_group')],
            'group.customer_group_id = customergroupprices.group_id',
            ['group_name' => 'customer_group_code']
        );

        return $connection->fetchAll($select);
    }
}