<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Model\QBXML;

use Magento\Sales\Model\Order\Creditmemo as CreditmemoModel;
use Magenest\QuickBooksDesktop\Model\QBXML;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;
use \Magento\Framework\ObjectManagerInterface;

/**
 * Class CreditMemo
 *
 * @package Magenest\QuickBooksDesktop\Model\QBXML
 */
class CreditMemo extends QBXML
{
    /**
     * @var QueueHelper
     */
    protected $_queueHelper;

    /**
     * @var CreditmemoModel
     */
    protected $_creditMemo;

    /**
     * @var Mapping
     */
    public $_map;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $_productFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * CreditMemo constructor.
     * @param CreditmemoModel $creditmemo
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Mapping $map
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param ObjectManagerInterface $objectManager
     * @param QueueHelper $queueHelper
     */
    public function __construct(
        CreditmemoModel $creditmemo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Mapping $map,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        QueueHelper $queueHelper
    )
    {
        parent::__construct($objectManager);
        $this->_creditMemo = $creditmemo;
        $this->scopeConfig = $scopeConfig;
        $this->_map = $map;
        $this->_productFactory = $productFactory;
        $this->_queueHelper = $queueHelper;
        $this->_version = $this->_queueHelper->getQuickBooksVersion();
        $this->customerFactory = $customerFactory;
    }

    /**
     * Get XML using sync to QBD
     *
     * @param $id
     * @return string
     */
    public function getXml($id)
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $model */
        $model = $this->_creditMemo->load($id);
        /** @var \Magento\Sales\Model\Order $order */
        $order = $model->getOrder();
        $billAddress = $model->getBillingAddress();
        $shipAddress = $model->getShippingAddress();
        $customerName = $order->getCustomerName();
        $customerId = $model->getOrder()->getCustomerId();
        if ($customerId) {
            $customer = $this->customerFactory->create()->load($customerId);
            if ($customer->getEntityId()) {
				$customerReceive = substr($this->removeSpecialChar($customer->getName()).' '. $customerId,0,40);
                //$customerReceive = $customer->getName() . ' ' . $customerId;
            } else {
                $customerReceive = $customerName . ' ' . $customerId;
            }
        } else {
            $customerReceive = $billAddress->getName() . ' ' . $order->getIncrementId();
        }
        $xml = $this->multipleXml($customerReceive, ['CustomerRef', 'FullName']);
        $xml .= $this->simpleXml($model->getIncrementId(), 'RefNumber');
        $xml .= $billAddress ? $this->getAddress($billAddress) : '';
        $xml .= $shipAddress ? $this->getAddress($shipAddress, 'ship') : '';
        $shipMethod = strtok($order->getShippingMethod(), '_');

        if (!empty($shipMethod)) {
            $xml .= $this->multipleXml($shipMethod, ['ShipMethodRef', 'FullName']);
        }

        $taxCode = $this->getTaxCodeTransaction($order);

        if ($taxCode != null && $this->_version == 2) {
            $xml .= $this->multipleXml($taxCode, ['ItemSalesTaxRef', 'FullName']);
        }

        /** @var \Magento\Sales\Model\Order\Creditmemo\Item $item */
        foreach ($model->getAllItems() as $item) {
            $xml .= $this->getOrderItem($item);
        }

        if ($model->getShippingAmount() != 0) {
            $dataShipping =
                [
                    'type' => 'Shipping',
                    'desc' => $order->getShippingDescription(),
                    'rate' => $model->getShippingAmount(),
                    'tax_amount' => $model->getShippingTaxAmount(),
                    'txn_id' => null,
                    'taxcode' => $taxCode
                ];

            $xml .= $this->getOtherItem($dataShipping, 'CreditMemoLineAdd');
        }

        if ($model->getDiscountAmount() != 0) {
            $discount = $model->getDiscountAmount();
            if ($discount < 0) {
                $discount = 0 - $discount;
            }
            $dataDiscount =
                [
                    'type' => 'Discount',
                    'desc' => $model->getDiscountDescription(),
                    'rate' => $discount,
                    'tax_amount' => $model->getDiscountTaxCompensationAmount(),
                    'txn_id' => null,
                    'taxcode' => $taxCode
                ];
            $xml .= $this->getOtherItem($dataDiscount, 'CreditMemoLineAdd');
        }


        return $xml;
    }

    /**
     * Get Order Item XML
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item *
     * @return string
     */
    protected function getOrderItem($item)
    {
        $price = $item->getPrice();
        $taxAmount = $item->getTaxAmount();

        $qty = $item->getQty();
        $sku = $item->getSku();

        if ($sku) {
            if ($qty > 0 && $price > 0) {
                $hasTax = false;
                $taxCode = $this->getTaxCodeItem($item->getParentId());
                $xml = '<CreditMemoLineAdd>';
                $xml .= $this->multipleXml(substr(str_replace(['&', '”', '\'', '<', '>', '"'], ' ', $sku), 0, 30), ['ItemRef', 'FullName']);
                $xml .= $this->simpleXml($qty, 'Quantity');
                $xml .= $this->simpleXml($price, 'Rate');

                if ($taxAmount != 0) {
                    $hasTax = true;
                }
                $xml .= $this->getXmlTax($taxCode, $hasTax);
                $xml .= '</CreditMemoLineAdd>';
            }
        } else {
            $xml = '<CreditMemoLineAdd>';
            $xml .= $this->multipleXml('Not Found Product From M2', ['ItemRef', 'FullName']);
            $xml .= '</CreditMemoLineAdd>';
            return $xml;
        }

        return $xml;
    }
}
