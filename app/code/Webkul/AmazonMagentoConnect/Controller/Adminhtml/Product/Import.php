<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webkul\AmazonMagentoConnect\Helper\ManageProductRawData;
use Webkul\AmazonMagentoConnect\Controller\Adminhtml\Product;

class Import extends Product
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ManageProductRawData
     */
    private $manageProductRawData;

    /**
     * @param Context              $context
     * @param JsonFactory          $resultJsonFactory
     * @param AmazonGlobal         $amazonGlobal
     * @param ManageProductRawData $manageProductRawData
     * @param Amazonmws            $amazonmws
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ManageProductRawData $manageProductRawData,
        \Webkul\AmazonMagentoConnect\Logger\Logger $logger,
        \Webkul\AmazonMagentoConnect\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->manageProductRawData = $manageProductRawData;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * Amazon  product import Controller.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $response = [];
        $items = [];
        $msg = '';
        $accountId = $this->getRequest()->getParam('id');
        try {
            if ($accountId) {
                $amzClient = $this->helper->getAmzClient($accountId);
                if ($amzClient) {
                    $response = $this->manageProductRawData->getFinalProductReport();
                } else {
                    $response = ['error' => 'true','error_msg' => __('Amazon Client Does not Initialize.')];
                }
            } else {
                $response = ['error' => 'true','error_msg' => __('Invalid parameter.')];
            }
        } catch (\Exception $e) {
            $this->logger->info('Controller Product Import : '.$e->getMessage());
            $response = [
                'error' => 'true',
                'error_msg' => 'Something went wrong, please check log files.',
                'actual_error' => $e->getMessage()
            ];
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
