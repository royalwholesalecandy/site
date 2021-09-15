<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Controller\Adminhtml\Product;

use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices as ResourceCustomerPrices;

class DeleteCustomerPrices extends \Magento\Catalog\Controller\Adminhtml\Product
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
     * @var \MageWorx\CustomerPrices\Model\ResourceModel\Product\Indexer\CustomerPrice
     */
    protected $indexer;

    /**
     * @var ResourceCustomerPrices
     */
    protected $customerPricesResourceModel;

    /**
     * DeleteCustomerPrices constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \MageWorx\CustomerPrices\Model\CustomerPricesFactory $pricesModelFactory
     * @param \MageWorx\CustomerPrices\Model\CustomerPricesRepository $pricesModelRepository
     * @param \MageWorx\CustomerPrices\Model\ResourceModel\Product\Indexer\CustomerPrice $indexer
     * @param ResourceCustomerPrices $customerPricesResourceModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \MageWorx\CustomerPrices\Model\CustomerPricesFactory $pricesModelFactory,
        \MageWorx\CustomerPrices\Model\CustomerPricesRepository $pricesModelRepository,
        \MageWorx\CustomerPrices\Model\ResourceModel\Product\Indexer\CustomerPrice $indexer,
        ResourceCustomerPrices $customerPricesResourceModel
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultJsonFactory           = $resultJsonFactory;
        $this->layoutFactory               = $layoutFactory;
        $this->pricesModelFactory          = $pricesModelFactory;
        $this->pricesModelRepository       = $pricesModelRepository;
        $this->indexer                     = $indexer;
        $this->customerPricesResourceModel = $customerPricesResourceModel;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $priceId = $this->getRequest()->getParam('id');
        $data    = $this->customerPricesResourceModel->getDataByEntityId($priceId);

        $result = $this->layoutFactory->create()->createBlock(
            'MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit\Tab'
        );

        $priceModel = $this->pricesModelFactory->create();
        $priceModel->load($priceId);

        try {
            $this->pricesModelRepository->delete($priceModel);
        } catch (\Exception $e) {
            $result = ['error' => 1, 'message' => __('Something went wrong while deleting Customer Price.')];
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);

        if (!empty($data)) {
            $customerId = $data['customer_id'];
            $productId  = $data['product_id'];

            $this->customerPricesResourceModel->deleteRowInMageworxCatalogProductEntityDecimalCustomerPrices(
                $productId,
                $customerId
            );
            $this->customerPricesResourceModel->deleteRowInMageworxCatalogProductIndexPrice(
                $productId,
                $customerId
            );
        }

        return $resultJson;
    }

    protected function _isAllowed()
    {
        return true;
    }

}