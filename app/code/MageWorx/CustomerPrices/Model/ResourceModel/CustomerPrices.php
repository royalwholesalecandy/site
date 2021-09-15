<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Model\ResourceModel;

use MageWorx\CustomerPrices\Helper\Calculate as HelperCalculate;
use MageWorx\CustomerPrices\Helper\Base as HelperBase;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices\Collection as CustomerPricesCollection;

class CustomerPrices extends AbstractDb
{
    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var HelperCalculate
     */
    protected $helperCalculate;

    /**
     * @var HelperBase
     */
    protected $helperBase;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|false
     */
    private $connection;

    /**
     * CustomerPrices constructor.
     *
     * @param Context $context
     * @param DateTime $date
     * @param HelperCalculate $helperCalculate
     * @param HelperBase $helperBase
     * @param StoreManager $storeManager
     */
    public function __construct(
        Context $context,
        DateTime $date,
        HelperCalculate $helperCalculate,
        HelperBase $helperBase,
        StoreManager $storeManager
    ) {
        $this->date            = $date;
        $this->helperCalculate = $helperCalculate;
        $this->helperBase      = $helperBase;
        $this->storeManager    = $storeManager;
        parent::__construct($context);

        $this->connection = $this->getConnection();
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageworx_customerprices', 'entity_id');
        $this->date = date('Y-m-d H:i:s', time());
    }

    /**
     * @param int $customerId
     * @param int $productId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getProductPrice($customerId, $productId)
    {
        $select = $this->connection->select()
                                   ->from(['customerprices' => $this->getMainTable()])
                                   ->where(
                                       'customerprices.attribute_type = ?',
                                       \MageWorx\CustomerPrices\Model\CustomerPrices::TYPE_CUSTOMER
                                   )
                                   ->where('customerprices.customer_id = ?', $customerId)
                                   ->where('customerprices.product_id = ?', $productId);
        $data   = $this->connection->fetchRow($select);

        return $data;
    }

    /**
     * Get collection product
     *
     * @param int $customerId
     * @param array $productIds
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getProductsPrice($customerId, $productIds)
    {
        $select = $this->connection->select()
                                   ->from(['customerprices' => $this->getMainTable()])
                                   ->where(
                                       'customerprices.attribute_type = ?',
                                       \MageWorx\CustomerPrices\Model\CustomerPrices::TYPE_CUSTOMER
                                   )
                                   ->where('customerprices.customer_id = ?', $customerId)
                                   ->where('customerprices.product_id IN(?)', $productIds);
        $data   = $this->connection->fetchAll($select);

        return $data;
    }

    /**
     * Get all data from table mageworx_customerprices
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadCustomerPricesCollection()
    {
        $select = $this->connection->select()
                                   ->from(['customerprices' => $this->getMainTable()]);
        $data   = $this->connection->fetchAll($select);

        return $data;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteNotCorrectSpecialPrice()
    {
        $tableName               = $this->_resources->getTableName('catalog_product_entity_decimal');
        $specialPriceAttributeId = $this->getSpecialPriceAttributeId();

        $this->connection->delete(
            $tableName,
            [
                'attribute_id = ?' => $specialPriceAttributeId,
                'store_id = ?'     => '1',
                'value IS NULL'
            ]
        );
    }

    /**
     * @param int $productId
     * @param int $customerId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteProductCustomerPrice($productId, $customerId)
    {
        $tableName = $this->getMainTable();
        $this->connection->delete(
            $tableName,
            [
                'product_id' . ' = ?'  => $productId,
                'customer_id' . ' = ?' => $customerId
            ]
        );
    }

    /**
     * @param array $customerIds
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteProductsByCustomer($customerIds)
    {
        $tableName = $this->getMainTable();
        $this->connection->delete(
            $tableName,
            [
                'customer_id IN (?)' => $customerIds
            ]
        );
    }

    /**
     * Save product group price
     *
     * @param $attributeType
     * @param $attributeId
     * @param $productId
     * @param $price
     * @param $priceType
     * @param $specialPrice
     * @param $specialPriceType
     * @param $discount
     * @param $discountPriceType
     * @param $priceSign
     * @param $priceValue
     * @param $specialPriceSign
     * @param $specialPriceValue
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveProductCustomerPrice(
        $attributeType,
        $customerId,
        $productId,
        $price,
        $priceType,
        $specialPrice,
        $specialPriceType,
        $discount,
        $discountPriceType,
        $priceSign,
        $priceValue,
        $specialPriceSign,
        $specialPriceValue
    ) {
        $tableName = $this->getMainTable();

        $data = [
            'attribute_type'      => $attributeType,
            'customer_id'         => $customerId,
            'product_id'          => $productId,
            'price'               => $price,
            'price_type'          => $priceType,
            'special_price'       => $specialPrice,
            'special_price_type'  => $specialPriceType,
            'discount'            => $discount,
            'discount_price_type' => $discountPriceType,
            'price_sign'          => $priceSign,
            'price_value'         => $priceValue,
            'special_price_sign'  => $specialPriceSign,
            'special_price_value' => $specialPriceValue
        ];

        $this->connection->insert($tableName, $data);
    }

    /**
     * Save products price by customer
     */
    /**
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveCustomerProductsPrices(array $data)
    {
        $this->connection->insertMultiple($this->getMainTable(), $data);
    }

    /**
     * @param int $attributeId
     * @param int $productId
     * @param $attributeType
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByAttribute(
        $attributeId,
        $productId,
        $attributeType
    ) {
        $select = $this->connection->select()
                                   ->from(['customerprices' => $this->getMainTable()])
                                   ->where('customerprices.attribute_type = ?', $attributeType)
                                   ->where('customerprices.customer_id = ?', $attributeId)
                                   ->where('customerprices.product_id = ?', $productId);
        $data   = $this->connection->fetchRow($select);

        return $data;
    }

    /**
     * @param $productPrice
     * @param $priceString
     * @return float|int
     */
    protected function getCalculatedProductPrice(
        $productPrice,
        $priceString
    ) {
        $pos = strpos($priceString, '%');
        if ($pos == true) {
            if (in_array(substr($priceString, 0, 1), ['+', '-'])) {
                $productPrice = $productPrice + $productPrice / 100 * (float)$priceString;
            } else {
                $productPrice = $productPrice / 100 * (float)$priceString;
            }
        } elseif (in_array(substr($priceString, 0, 1), ['+', '-'])) {
            $productPrice = $productPrice + (float)$priceString;
        } else {
            $productPrice = $priceString;
        }

        return $productPrice;
    }

    /**
     * @return int|null
     */
    public function getPriceAttributeId()
    {
        $attributeCodePrice = 'price';
        $select             = $this->connection->select()
                                               ->from(
                                                   [
                                                       'attributeTable' => $this->_resources->getTableName(
                                                           'eav_attribute'
                                                       )
                                                   ]
                                               )
                                               ->where('attributeTable.attribute_code = ?', $attributeCodePrice);
        $data               = $this->connection->fetchRow($select);

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
        $attributeCodeSpecialPrice = 'special_price';
        $select                    = $this->connection->select()
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
        $data                      = $this->connection->fetchRow($select);

        if (!empty($data['attribute_id'])) {
            return $data['attribute_id'];
        }

        return null;
    }

    /**
     * @param $productId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDataByProductId($productId)
    {
        $select = $this->connection->select()
                                   ->from(['customerprices' => $this->getMainTable()])
                                   ->where('customerprices.product_id = ?', $productId);

        $data = $this->connection->fetchAll($select);

        return $data;
    }

    /**
     * @param int $customerId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDataByCustomerId($customerId)
    {
        $select = $this->connection->select()
                                   ->from(['customerprices' => $this->getMainTable()])
                                   ->where('customerprices.customer_id = ?', $customerId);

        $data = $this->connection->fetchAll($select);

        return $data;
    }

    /**
     * @param int $productId
     * @return null
     */
    public function getTypeId($productId)
    {
        $id     = $this->helperCalculate->getLinkField();
        $select = $this->connection->select()->from(
            [$this->getTable('catalog_product_entity')]
        )->where($id, $productId);

        $data = $this->connection->fetchRow($select);

        if (!empty($data['type_id'])) {
            return $data['type_id'];
        }

        return null;
    }

    /**
     * @param int $entityId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDataByEntityId($entityId)
    {
        $select = $this->connection->select()
                                   ->from(['customerprices' => $this->getMainTable()])
                                   ->where('customerprices.entity_id = ?', $entityId);
        $data   = $this->connection->fetchRow($select);

        return $data;
    }

    /**
     * Delete row in mageworx_catalog_product_index_price by  entityId AND customerId
     *
     * @param int $entityId
     * @param int $customerId
     */
    public function deleteRowInMageworxCatalogProductIndexPrice($entityId, $customerId)
    {
        $this->connection->delete(
            $this->_resources->getTableName('mageworx_catalog_product_index_price'),
            [
                'entity_id = ?'   => $entityId,
                'customer_id = ?' => $customerId
            ]
        );
    }

    /**
     * Delete row in mageworx_catalog_product_entity_decimal_customer_prices by  entityId AND customerId
     *
     * @param int $entityId
     * @param int $customerId
     */
    public function deleteRowInMageworxCatalogProductEntityDecimalCustomerPrices($entityId, $customerId)
    {
        $entity                  = $this->helperCalculate->getLinkField();
        $priceAttributeId        = $this->getPriceAttributeId();
        $specialPriceAttributeId = $this->getSpecialPriceAttributeId();
        $specialPriceAttribute   = [$priceAttributeId, $specialPriceAttributeId];

        $this->connection->delete(
            $this->_resources->getTableName('mageworx_catalog_product_entity_decimal_customer_prices'),
            [
                $entity . ' = ?'     => $entityId,
                'customer_id = ?'    => $customerId,
                'attribute_id IN(?)' => $specialPriceAttribute
            ]
        );
    }

    /**
     * @param array $ids
     * @param int $customerId
     * @return array
     */
    public function getCalculatedProductsDataByCustomer(array $ids, $customerId)
    {
        $tableName = $this->_resources->getTableName('mageworx_catalog_product_entity_decimal_customer_prices');
        $rowId     = $this->helperCalculate->getLinkField();

        $select = $this->connection->select()
                                   ->from($tableName)
                                   ->where('customer_id = ?', $customerId)
                                   ->where($rowId . ' IN(?)', $ids);

        return $this->connection->fetchAll($select);
    }

    /**
     * @param int $id
     * @param int $customerId
     * @return array
     */
    public function getCalculatedProductDataByCustomer($id, $customerId)
    {
        $tableName = $this->_resources->getTableName('mageworx_catalog_product_entity_decimal_customer_prices');
        $rowId     = $this->helperCalculate->getLinkField();

        $select = $this->connection->select()
                                   ->from($tableName)
                                   ->where('customer_id = ?', $customerId)
                                   ->where($rowId . ' = ?', $id);

        return $this->connection->fetchAll($select);
    }

    /**
     * @param int $productId
     * @return bool
     */
    public function hasSpecialAttributeByProductId($productId)
    {
        $tableName               = $this->_resources->getTableName('catalog_product_entity_decimal');
        $specialPriceAttributeId = $this->getSpecialPriceAttributeId();
        $rowId                   = $this->helperCalculate->getLinkField();

        $select = $this->connection->select()
                                   ->from($tableName)
                                   ->where($rowId . ' = ?', $productId)
                                   ->where('attribute_id = ?', $specialPriceAttributeId);

        return !empty($this->connection->fetchRow($select));
    }

    /**
     * @param int $productId
     */
    public function addRowWithSpecialAttribute($productId)
    {
        $tableName               = $this->_resources->getTableName('catalog_product_entity_decimal');
        $specialPriceAttributeId = $this->getSpecialPriceAttributeId();
        $rowId                   = $this->helperCalculate->getLinkField();

        $data = [
            'value_id'     => '',
            'attribute_id' => $specialPriceAttributeId,
            'store_id'     => $this->getStoreIdProductPrice($productId),
            $rowId         => $productId,
            'value'        => null
        ];

        $this->connection->insert($tableName, $data);
    }

    /**
     * @param int $customerId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hasAssignCustomer($customerId)
    {
        $select = $this->connection->select()
                                   ->from(['customerprices' => $this->getMainTable()])
                                   ->where('customerprices.customer_id = ?', $customerId);
        $data   = $this->connection->fetchRow($select);

        return (bool)$data;
    }

    /**
     * @param $productId
     * @return string
     */
    protected function getStoreIdProductPrice($productId)
    {
        $tableName        = $this->_resources->getTableName('catalog_product_entity_decimal');
        $priceAttributeId = $this->getPriceAttributeId();
        $rowId            = $this->helperCalculate->getLinkField();

        $select = $this->connection->select()
                                   ->from($tableName)
                                   ->where($rowId . ' = ?', $productId)
                                   ->where('attribute_id = ?', $priceAttributeId);

        $data = $this->connection->fetchRow($select);

        return !empty($data) ? $data['store_id'] : '0';
    }

    /**
     * Join table mageworx_customerprices
     *
     * @param \MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices\Collection $collection
     * @return mixed
     * @throws \Exception
     */
    public function joinMageWorxCustomerPricesCollect($collection)
    {
        $rowId      = $this->helperCalculate->getLinkField();
        $customerId = $this->helperBase->getAdminCustomerId();

        if (!is_null($customerId)) {
            /* @var $collection */
            $collection->getSelect()->joinLeft(
                ['customPrice' => $this->_resources->getTableName('mageworx_customerprices')],
                'customPrice.product_id = e.' . $rowId . ' AND customPrice.customer_id = ' . $customerId,
                ['custom_price' => 'price', 'custom_special_price' => 'special_price']
            );
        }

        return $collection;
    }

    /**
     * @param CustomerPricesCollection $collection
     * @return CustomerPricesCollection
     */
    public function joinEmailCustomer($collection)
    {
        /* @var CustomerPricesCollection $collection */
        $collection->getSelect()->joinLeft(
            ['email' => $this->_resources->getTableName('customer_entity')],
            'email.entity_id = main_table.customer_id',
            ['email']
        );

        return $collection;
    }

    /**
     * @param CustomerPricesCollection $collection
     * @return CustomerPricesCollection
     */
    public function joinSkuProduct($collection)
    {
        /* @var CustomerPricesCollection $collection */
        $collection->getSelect()->joinLeft(
            ['product' => $this->_resources->getTableName('catalog_product_entity')],
            'product.entity_id = main_table.product_id',
            ['sku']
        );

        return $collection;
    }

    /**
     * Get array customer prices data with email & sku
     *
     * @param array $customerIds
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFullCustomersPricesData($customerIds = [])
    {
        $select = $this->connection->select()
                                   ->from(['customerprices' => $this->getMainTable()]);

        if (!empty($customerIds)) {
            $select->where('customerprices.customer_id IN(?)', $customerIds);
        }

        $select->joinLeft(
            ['product' => $this->_resources->getTableName('catalog_product_entity')],
            'product.entity_id = customerprices.product_id',
            ['sku']
        );

        $select->joinLeft(
            ['email' => $this->_resources->getTableName('customer_entity')],
            'email.entity_id = customerprices.customer_id',
            ['email']
        );

        $data = $this->connection->fetchAll($select);

        return $data;
    }

    /**
     * Get product ids assign on customer
     *
     * @param int $customerId
     * @param array $productIds
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductIdsByCustomerId($customerId)
    {
        $select     = $this->connection->select()
                                       ->from(['customerprices' => $this->getMainTable()], ['product_id'])
                                       ->where(
                                           'customerprices.attribute_type = ?',
                                           \MageWorx\CustomerPrices\Model\CustomerPrices::TYPE_CUSTOMER
                                       )
                                       ->where('customerprices.customer_id = ?', $customerId)
                                       ->group('product_id');
        $productIds = $this->connection->fetchCol($select);

        return $productIds;
    }


    /**
     *
     * get array with id:{price:-10%,special_price:-20%}
     *
     * @param int $customerId
     * @param array $productIds
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductsPricesByCustomerId($customerId)
    {
        $productData  = [];
        $select       = $this->connection->select()
                                         ->from(
                                             ['customerprices' => $this->getMainTable()],
                                             ['product_id', 'price', 'special_price']
                                         )
                                         ->where('customerprices.customer_id = ?', $customerId)
                                         ->group('product_id');
        $customerData = $this->connection->fetchAll($select);

        if (!is_array($customerData)) {
            return [];
        }

        foreach ($customerData as $data) {
            $productData[$data['product_id']] = array(
                "price"         => $data['price'],
                "special_price" => $data['special_price']
            );
        }

        return $productData;
    }
}