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
use Webkul\AmazonMagentoConnect\Helper;
use Magento\Framework\Controller\ResultFactory;
use Webkul\AmazonMagentoConnect\Api\ProductMapRepositoryInterface;

class SyncToAmazon extends ProductToAmazon
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * Webkul\AmazonMagentoConnect\Api\ProductMapRepositoryInterface
     */
    private $productMapRepository;

    /**
     * @param Action\Context                             $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Helper\Data $helper,
        Helper\ProductOnAmazon $productOnAmazon,
        ProductMapRepositoryInterface $productMapRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->productOnAmazon = $productOnAmazon;
        $this->productMapRepository = $productMapRepository;
    }

    /**
     * SyncInAmazon action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $productIds = [];
        if (isset($params['account_id']) && $params['account_id']) {
            $this->helper->getAmzClient($params['account_id']);
            $isUpdate = isset($params['is_update']) ? 1 : 0;
            $operationName = $isUpdate ? 'updated' : 'exported';
            if ($isUpdate) {
                $collection =  $this->productMapRepository
                                    ->getCollectionByIds($params['productEntityIds']);
                foreach ($collection as $proMap) {
                    $productIds[] = $proMap->getMagentoProId();
                }
            } else {
                $productIds = $params['mageProEntityIds'];
            }

            $result = $this->productOnAmazon->manageMageProduct($productIds, $params['is_fba'], $isUpdate);
    
            if (isset($result['error']) && $result['error']) {
                $this->messageManager->addError(
                    __("Something went wrong.")
                );
            } else {
                if (!empty($result['count'])) {
                    $this->messageManager->addSuccess(
                        __("A total of %1 record(s) have been %2 to amazon.", $result['count'], $operationName)
                    );
                }
    
                if (isset($result['error_count']) && !empty($result['error_count'])) {
                    $this->messageManager->addError(
                        __("A total of %1 record(s) have been failed to %2 at amazon.", $result['error_count'], $operationName)
                    );
                    $this->messageManager->addWarning(
                        __("Please set product identifier code(UPC,EAN,ASIN etc) for the failed product(s).")
                    );
                }
            }
        } else {
            $this->messageManager->addError(
                __("Invalid parameters.")
            );
        }
        
        return $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        )->setPath(
            '*/accounts/edit',
            [
                    'id'=>$params['account_id'],
                    'active_tab' => 'product_sync'
                ]
        );
    }
}
