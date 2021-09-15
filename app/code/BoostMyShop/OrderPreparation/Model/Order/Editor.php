<?php

namespace BoostMyShop\OrderPreparation\Model\Order;

class Editor
{
    protected $_quoteHelper;
    protected $_orderFactory;
    protected $_customerRepository;
    protected $_logger;

    public function __construct(
        \BoostMyShop\OrderPreparation\Model\Order\Quote $quoteHelper,
        \BoostMyShop\OrderPreparation\Helper\Logger $logger,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->_quoteHelper = $quoteHelper;
        $this->_orderFactory = $orderFactory;
        $this->_customerRepository = $customerRepository;
        $this->_logger = $logger;
    }

    public function changeOrderItemQty($orderItem, $newQty, $inProgressItem = null)
    {
        $orderItem->setqty_ordered($newQty)->save();

        if ($inProgressItem)
            $inProgressItem->setipi_qty($newQty)->save();

    }

    public function changeOrderItemSku($orderItem, $newProduct, $inProgressItem = null, $options = null)
    {
        $orderItem->setSku($newProduct->getSku());
        $orderItem->setName($newProduct->getName());
        $orderItem->setProductId($newProduct->getId());

        $productOptions = $orderItem->getProductOptions();
        $productOptions['info_buyRequest']['options'] = $options;
        $productOptions['options'] = $this->buildOptionsForOrderItem($newProduct, $options);
        $orderItem->setProductOptions($productOptions);

        $orderItem->save();
    }

    protected function buildOptionsForOrderItem($product, $options)
    {
        $result = [];

        foreach($product->getOptions() as $option)
        {
            if (!isset($options[$option->getId()]))
                continue;

            $selectedValue = $options[$option->getId()];

            $resultOption = [];
            $resultOption['label'] = $option->getTitle();
            $resultOption['option_id'] = $option->getId();
            $resultOption['option_type'] = $option->getType();
            $resultOption['option_value'] = $selectedValue;
            $resultOption['custom_view'] = false;

            switch($option->getType())
            {
                case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_RADIO;
                    foreach($option->getValues() as $optionValue)
                    {
                        if ($optionValue->getId() == $selectedValue)
                        {
                            $resultOption['value'] = $optionValue->getTitle();
                            $resultOption['print_value'] = $optionValue->getTitle();
                        }
                    }
                    break;
                case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_CHECKBOX;
                    foreach($option->getValues() as $optionValue)
                    {
                        if ($optionValue->getId() == $selectedValue)
                        {
                            $resultOption['value'] = $optionValue->getTitle();
                            $resultOption['print_value'] = $optionValue->getTitle();
                        }
                    }
                    break;
                default:
                    throw new \Exception('Option type '.$option->getType().' is not supported');
                    break;
            }


            $result[] = $resultOption;
        }

        return $result;
    }

    public function updateOrderTotals($orderId)
    {
        $order = $this->_orderFactory->create()->load($orderId);
        $quoteHelper = $this->getQuote($order);
        $quote = $quoteHelper->getMagentoQuote();

        $quoteToOrderMapping = [
            'base_grand_total' => 'base_grand_total',
            'base_subtotal' => 'base_subtotal',
            'grand_total' => 'grand_total',
            'subtotal' => 'subtotal'
        ];
        foreach($quoteToOrderMapping as $k => $v) {
            $order->setData($k, $quote->getData($v));
            $this->_logger->log('Copy to order #'.$orderId.' : '.$k.' = '.$quote->getData($v), \BoostMyShop\OrderPreparation\Helper\Logger::kLogEditor);
        }

        $quoteAddressToOrder = [
            'shipping_incl_tax' => 'shipping_incl_tax',
            'base_shipping_incl_tax' => 'base_shipping_incl_tax',
            'base_shipping_amount' => 'base_shipping_amount',
            'base_shipping_tax_amount' => 'base_shipping_tax_amount',
            'shipping_amount' => 'shipping_amount',
            'shipping_tax_amount' => 'shipping_tax_amount'
        ];
        foreach($quoteAddressToOrder as $k => $v) {
            $order->setData($k, $quote->getShippingAddress()->getData($v));
            $this->_logger->log('Copy to order #'.$orderId.' : '.$k.' = '.$quote->getShippingAddress()->getData($v), \BoostMyShop\OrderPreparation\Helper\Logger::kLogEditor);
        }


        /*
         * Fields to update for order record
                total_qty_ordered',
                    base_subtotal_incl_tax',
                    base_total_due',
                    subtotal_incl_tax',
                    total_due',
                    base_tax_amount',
                    tax_amount',
        */

        $order->save();


        foreach($order->getAllItems() as $orderItem)
        {

            //todo : will not work if the same product is several times in the quote with different custom options
            $quoteItem = $quoteHelper->getQuoteItemByProductId($orderItem->getproduct_id());

            $quoteToOrderItemMapping = [
                'price' => 'price',
                'base_price' => 'base_price',
                'tax_percent' => 'tax_percent',
                'tax_amount' => 'tax_amount',
                'row_total' => 'row_total',
                'base_row_total' => 'base_row_total',
                'price_incl_tax' => 'price_incl_tax',
                'base_price_incl_tax' => 'base_price_incl_tax',
                'row_total_incl_tax' => 'row_total_incl_tax',
                'base_row_total_incl_tax' => 'base_row_total_incl_tax'
            ];
            foreach($quoteToOrderItemMapping as $k => $v) {
                $orderItem->setData($k, $quoteItem->getData($v));
            }
            $orderItem->setOriginalPrice($quoteItem->getData('price'));
            $orderItem->setBaseOriginalPrice($quoteItem->getData('base_price'));

            $orderItem->save();

        }

    }

    protected function getQuote($order)
    {
        $this->_quoteHelper->initQuote($order->getStoreId());

        if ($order->getCustomerId()) {
            $customer = $this->_customerRepository->getById($order->getCustomerId());
            $this->_quoteHelper->setCustomer($customer);
        }
        else
            throw new \Exception('Editor for guest order is not supported yet');

        $this->_quoteHelper->setAddress($order->getBillingAddress()->getData(), 'billing');
        $this->_quoteHelper->setAddress($order->getShippingAddress()->getData(), 'shipping');

        foreach($order->getAllItems() as $item)
        {
            $options = $item->getProductOptions();
            $cartOptions = null;
            if (isset($options['options'])) {
                foreach($options['options'] as $option)
                    $cartOptions[$option['option_id']] = $option['option_value'];
            }
            $this->_quoteHelper->addProduct($item->getProductId(), $item->getQtyOrdered(), null, $cartOptions);
        }

        $this->_quoteHelper->setShippingMethod($order->getShippingMethod());
        $this->_quoteHelper->setPaymentMethod($order->getPayment()->getMethod());

        return $this->_quoteHelper;
    }

}
