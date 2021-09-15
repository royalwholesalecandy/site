<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */


namespace MageWorx\CustomerPrices\Controller\Adminhtml\Product;

use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices as ResourceCustomerPrices;

class AddCustomerPrices extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \MageWorx\CustomerPrices\Model\CustomerPricesFactory
     */
    protected $pricesModelFactory;

    /**
     * @var \MageWorx\CustomerPrices\Model\CustomerPricesRepository
     */
    protected $pricesModelRepository;

    /**
     * @var \MageWorx\CustomerPrices\Helper\Calculate
     */
    protected $helperCalculate;

    /**
     * @var \MageWorx\CustomerPrices\Model\ResourceModel\Product\Indexer\CustomerPrice
     */
    protected $indexer;

    /**
     * @var ResourceCustomerPrices
     */
    protected $customerPricesResourceModel;

    /**
     * AddCustomerPrices constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \MageWorx\CustomerPrices\Model\CustomerPricesFactory $pricesModelFactory
     * @param \MageWorx\CustomerPrices\Model\CustomerPricesRepository $pricesModelRepository
     * @param \MageWorx\CustomerPrices\Helper\Calculate $helperCalculate
     * @param \MageWorx\CustomerPrices\Model\ResourceModel\Product\Indexer\CustomerPrice $indexer
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \MageWorx\CustomerPrices\Model\CustomerPricesFactory $pricesModelFactory,
        \MageWorx\CustomerPrices\Model\CustomerPricesRepository $pricesModelRepository,
        \MageWorx\CustomerPrices\Helper\Calculate $helperCalculate,
        \MageWorx\CustomerPrices\Model\ResourceModel\Product\Indexer\CustomerPrice $indexer,
        ResourceCustomerPrices $customerPricesResourceModel
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultJsonFactory           = $resultJsonFactory;
        $this->layoutFactory               = $layoutFactory;
        $this->pricesModelFactory          = $pricesModelFactory;
        $this->pricesModelRepository       = $pricesModelRepository;
        $this->helperCalculate             = $helperCalculate;
        $this->indexer                     = $indexer;
        $this->customerPricesResourceModel = $customerPricesResourceModel;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $productId    = $this->getRequest()->getParam('product_id');
        $price        = $this->getRequest()->getParam('price');
        $specialPrice = $this->getRequest()->getParam('special_price');
        $customerId   = $this->getRequest()->getParam('customer_id');

        // entity type
        $attributeType = \MageWorx\CustomerPrices\Model\CustomerPrices::TYPE_CUSTOMER;

        $priceType        = $this->helperCalculate->getPriceType($price);
        $specialPriceType = $this->helperCalculate->getPriceType($specialPrice);

        $priceSign        = $this->helperCalculate->getPriceSign($price);
        $specialPriceSign = $this->helperCalculate->getPriceSign($specialPrice);

        $priceValue        = $this->helperCalculate->getPositivePriceValue($price);
        $specialPriceValue = $this->helperCalculate->getPositivePriceValue($specialPrice);

        $result = $this->layoutFactory->create()->createBlock(
            'MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit\Tab'
        );

        if (empty($productId) || empty($customerId) || (empty($price) && empty($specialPrice))) {
            $result = ['error' => 1, 'message' => __('Error saving Customer Price. Empty data for saving')];
        } else {
            // data for save/update
            $data = [
                'attribute_type'      => $attributeType,
                'customer_id'         => $customerId,
                'product_id'          => $productId,
                'price'               => $price,
                'special_price'       => $specialPrice,
                'price_type'          => $priceType,
                'special_price_type'  => $specialPriceType,
                'price_sign'          => $priceSign,
                'price_value'         => $priceValue,
                'special_price_sign'  => $specialPriceSign,
                'special_price_value' => $specialPriceValue
            ];

            $priceModel = $this->pricesModelFactory->create();
            $prices     = $priceModel->loadByCustomer($customerId, $productId);

            if ($prices && isset($prices['entity_id'])) {
                $priceId = $prices['entity_id'];
                $priceModel->setData($data);
                $priceModel->setId($priceId);
            } else {
                $priceModel->setData($data);
            }

            try {
                $this->pricesModelRepository->save($priceModel);

                /* set data in catalog_product_entity_decimal */
                if (!$this->customerPricesResourceModel->hasSpecialAttributeByProductId($productId)) {
                    $this->customerPricesResourceModel->addRowWithSpecialAttribute($productId);
                }

                /* set data in mageworx_catalog_product_index_price  */
                $strTypeId = $this->customerPricesResourceModel->getTypeId($productId);
                if ($strTypeId !== null) {
                    $this->indexer->setTypeId($strTypeId);
                    $this->indexer->reindexEntityCustomer([$productId], [$customerId]);
                }

            } catch (\Exception $e) {
                $result = ['error' => 1, 'message' => __('Unable to Add Customer Prices')];
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);

        return $resultJson;
    }

    protected function _isAllowed()
    {
        return true;
    }

}