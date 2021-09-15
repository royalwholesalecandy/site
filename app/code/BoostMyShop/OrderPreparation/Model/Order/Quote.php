<?php

namespace BoostMyShop\OrderPreparation\Model\Order;

use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

class Quote
{
    protected $_transaction;
    protected $_storeManager;
    protected $_quoteFactory;
    protected $_quoteManagement;
    protected $_shippingMethodManagement;
    protected $_paymentMethodManagement;
    protected $_quote;
    protected $_paymentHelper;
    protected $_context;
    protected $_pricingHelper;
    protected $_logger;
    protected $_storeFactory;

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Quote\Model\ShippingMethodManagement $shippingMethodManagement,
        \Magento\Quote\Model\PaymentMethodManagement $paymentMethodManagement,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \BoostMyShop\OrderPreparation\Helper\Logger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_context = $context;
        $this->_product = $product;
        $this->_quoteFactory = $quote;
        $this->_customerFactory = $customerFactory;
        $this->_shippingMethodManagement = $shippingMethodManagement;
        $this->_paymentMethodManagement = $paymentMethodManagement;
        $this->_quoteManagement = $quoteManagement;
        $this->_paymentHelper = $paymentHelper;
        $this->_pricingHelper = $pricingHelper;
        $this->_logger = $logger;
        $this->_storeFactory = $storeFactory;
        $this->_storeManager = $storeManager;
    }

    public function initQuote($store)
    {
        if (is_numeric($store))
            $store = $this->_storeFactory->create()->load($store);

        $this->_quote = $this->_quoteFactory->create();
        $this->_quote->setStore($store);

        return $this;
    }

    public function load($quoteId)
    {
        $this->_quote = $this->_quoteFactory->create()->load($quoteId);

        return $this;
    }

    public function setCustomer($customer)
    {
        $this->_quote->assignCustomer($customer);
        return $this;
    }

    public function setGuestCustomer($email, $address)
    {
        $this->_quote->setCustomerIsGuest(true);
        $this->_quote->setcustomer_email($email);
        $this->_quote->setcheckout_method('guest');

        $this->setAddress($address  );

        return $this;
    }

    public function addProduct($productId, $qty, $customPrice = null, $options = null)
    {
        $product = $this->_product->create()->load($productId);

        $request =  new \Magento\Framework\DataObject();

        $request->setQty($qty);
        if ($customPrice)
            $request->setCustomPrice($customPrice);
        if ($options)
            $request->setOptions($options);

        $result = $this->_quote->addProduct($product, $request);
        if (!is_object($result))
            throw new \Exception($result);

        return $this;
    }

    public function getQuoteItemByProductId($productId)
    {
        foreach($this->_quote->getAllItems() as $item)
        {
            if ($item->getProductId() == $productId)
                return $item;
        }
        return false;
    }

    public function addOrUpdateProduct($productId, $qty, $customPrice)
    {
        //$product = $this->_product->create()->load($productId);
        //$quoteItem = $this->_quote->getItemByProduct($product);
        $quoteItem = $this->getQuoteItemByProductId($productId);

        if (!$quoteItem)
        {
            //add the product to the quote
            if ($qty > 0) {
                $request = new \Magento\Framework\DataObject();
                $request->setQty($qty);
                if ($customPrice)
                    $request->setCustomPrice($customPrice);
                $this->addProduct($productId, $qty, $customPrice);
                $this->_logger->log('add product #' . $productId . ' to quote (qty: ' . $qty . ')');
            }
        }
        else
        {
            //update the product in the quote
            if ($qty > 0)
            {
                $hasChanges = (($qty != $quoteItem->getQty()) || ($customPrice != $quoteItem->getcustom_price()));
                if ($hasChanges)
                {
                    $request =  new \Magento\Framework\DataObject();
                    $request->setQty($qty);
                    if ($customPrice)
                        $request->setCustomPrice($customPrice);
                    $this->_quote->updateItem($quoteItem->getId(), $request);
                    $this->_logger->log('Update product #'.$productId.' in quote (qty: '.$qty.')');
                }
                else
                    $this->_logger->log('No changes for product #'.$productId.' in quote');

            }
            else
            {
                $this->_quote->removeItem($quoteItem->getId());
                $this->_logger->log('Remove product #'.$productId.' (item #'.$quoteItem->getId().') from quote');
            }
        }

        return $this;
    }

    public function setAddress($address, $type = null)
    {
        switch($type)
        {
            case 'billing':
                $this->_quote->getBillingAddress()->addData($address);
                break;
            case 'shipping':
                $this->_quote->getShippingAddress()->addData($address);
                break;
            default:
                $this->_quote->getBillingAddress()->addData($address);
                $this->_quote->getShippingAddress()->addData($address);
                break;
        }

        return $this;
    }

    public function setPaymentMethod($methodCode, $paymentData = [])
    {
        $this->_quote->setPaymentMethod($methodCode);

        $data = $paymentData;
        $data['method'] = $methodCode;

        $this->_quote->getPayment()->importData($data);
        if (isset($data['additional_information']))
            $this->_quote->getPayment()->setAdditionalInformation($data['additional_information']);
        return $this;
    }

    public function setShippingMethod($methodCode)
    {
        $this->_quote->getShippingAddress()->setCollectShippingRates(true);
        $this->_quote->getShippingAddress()->setShippingMethod($methodCode);
        $this->_quote->getShippingAddress()->collectShippingRates();
        $this->_quote->save();

        return $this;
    }

    public function applyCoupon($couponCode)
    {
        $this->_quote->setCouponCode($couponCode);
        $this->_quote->save();
        return $this;
    }

    public function getResult()
    {
        //save to get up to date quote
        $this->_quote->getBillingAddress();
        $this->_quote->getShippingAddress()->setCollectShippingRates(true);
        $this->_quote->getShippingAddress()->collectShippingRates();
        $this->_quote->collectTotals();
        $this->_quote->save();

        $result = ['totals' => [], 'items' => [], 'customer' => [], 'shipping' => [], 'quote_id' => $this->_quote->getId()];

        $result['currency'] = $this->_quote->getStore()->getCurrentCurrencyCode();
        $result['currency_symbol'] = $this->_quote->getStore()->getCurrentCurrency()->getCurrencySymbol();
        $result['currency_format'] = $this->_quote->getStore()->getCurrentCurrency()->getOutputFormat();

        //totals
        $result['totals']['subtotal'] = $this->_quote->getSubtotal();
        $result['totals']['grand_total'] = $this->_quote->getGrandTotal();

        //quote items
        $quoteItemFields = ['sku', 'name', 'qty', 'product_id'];
        foreach($this->_quote->getAllItems() as $item)
        {
            $quoteItem = [];
            foreach($quoteItemFields as $field)
                $quoteItem[$field] = $item->getData($field);
            $quoteItem['price'] = $item->getPrice();
            $quoteItem['sku'] = $item->getSku();
            $quoteItem['name'] = $item->getName();
            $quoteItem['tax_percent'] = $item->gettax_percent();
            $quoteItem['price_incl_tax'] = $item->getprice_incl_tax();
            $result['items'][] = $quoteItem;
        }

        //customer
        $result['customer']['guest'] = ($this->_quote->getCustomerIsGuest() ? 1 : 0);
        $result['customer']['id'] =  $this->_quote->getCustomerId();
        if ($result['customer']['id'])
            $result['customer']['title'] = $this->_quote->getCustomerFirstname().' '.$this->_quote->getCustomerLastname();
        else
            $result['customer']['title'] = 'Guest customer';

        //shipping method
        $result['shipping']['grand_total'] =  $this->_quote->getShippingAddress()->getShippingInclTax();
        $result['shipping']['method'] = $this->_quote->getShippingAddress()->getShippingMethod();
        $result['shipping']['title'] = $this->_quote->getShippingAddress()->getShippingDescription();
        $result['shipping']['available_methods'] = [];
        foreach ($this->_shippingMethodManagement->getList($this->_quote->getId()) as $rate) {
            $result['shipping']['available_methods'][] = [
                                                            'method' => $rate->getCarrierCode().'_'.$rate->getMethodCode(),
                                                            'title' => $rate->getCarrierTitle().' - '.$rate->getMethodTitle(),
                                                            'price' => $rate->getPriceInclTax()
                                                            ];
        }

        //payment method
        $result['payment']['method'] = $this->_quote->getPayment()->getMethod();
        if ($this->_quote->getPayment()->getMethod())
        {
            $result['payment']['title'] = $this->_quote->getPayment()->getMethodInstance()->getTitle();
            $result['payment']['form'] = $this->_paymentHelper->getMethodFormBlock($this->_quote->getPayment()->getMethodInstance(), $this->_context->getLayout())->toHtml();
            $result['payment']['available_methods'] = [];
        }
        foreach($this->_paymentMethodManagement->getList($this->_quote->getId()) as $item)
        {
            if ($this->_quote->getPayment()->getMethod() == $item->getCode())
                $form = $result['payment']['form'];
            else
                $form = $this->_paymentHelper->getMethodFormBlock($item, $this->_context->getLayout())->toHtml();
            $result['payment']['available_methods'][] = ['code' => $item->getCode(), 'title' => $item->getTitle(), 'form' => $form];
        }

        //store
        $result['store']['id'] = $this->_quote->getStore()->getId();
        $result['store']['name'] = $this->_quote->getStore()->getName();
        $result['store']['group'] = $this->_quote->getStore()->getGroup()->getName();
        $result['store']['website'] = $this->_quote->getStore()->getWebsite()->getName();

        $result['coupon_code'] = $this->_quote->getCouponCode();

        return $result;
    }

    public function placeOrder()
    {
        $order = $this->_quoteManagement->submit($this->_quote);
        return $order;
    }

    public function getMagentoQuote()
    {
        return $this->_quote;
    }

}