<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Controller\Adminhtml\ProductToAmazon;

use Webkul\AmazonMagentoConnect\Controller\Adminhtml\ProductToAmazon;
use Webkul\AmazonMagentoConnect\Helper\ProductOnAmazon as ProductToAmazonHelper;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Webkul\AmazonMagentoConnect\Api\ProductMapRepositoryInterface;

class UpdateStatusOfExportedPro extends ProductToAmazon
{
    /**
     * @var  \Magento\Framework\View\Result\LayoutFactory
     */
    private $resultLayoutFactory;

    /**
     * \Webkul\AmazonMagentoConnect\Helper\ProductToAmazon
     */
    private $productToAmazon;

    /**
     * Class constructor
     * @param \Magento\Backend\App\Action\Context          $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @return void
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        ProductToAmazonHelper $productToAmazonHelper,
        JsonFactory $resultJsonFactory,
        ProductMapRepositoryInterface $productMapRepo,
        \Webkul\AmazonMagentoConnect\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->productToAmazon = $productToAmazonHelper;
        $this->productMapRepo = $productMapRepo;
        $this->helper = $helper;
    }

    public function execute()
    {
        $updatedRecords = [];
        $resultJson = $this->resultJsonFactory->create();
        $params = $this->getRequest()->getParams();
        $this->helper->getAmzClient($params['id']);
        $productMapColl = $this->helper->getExportedProColl($params['id']);

        $feedIds = $this->getFeedIds($productMapColl);

        $response = $this->productToAmazon->checkProductFeedStatus($feedIds);
        return $resultJson->setData($response);
    }

    /**
     * get feed ids of exported product
     *
     * @param object $productMapColl
     * @return array
     */
    private function getFeedIds($productMapColl)
    {
        $feedArray = [];
        foreach ($productMapColl as $feed) {
            $feedArray[] = $feed->getFeedsubmissionId();
        }
        return array_filter($feedArray);
    }
}
