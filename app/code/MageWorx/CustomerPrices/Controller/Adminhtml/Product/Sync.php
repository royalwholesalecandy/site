<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use MageWorx\CustomerPrices\Model\Synchronizer;

class Sync extends Action
{
    /**
     * @var Synchronizer
     */
    protected $synchronizer;

    /**
     * Sync constructor.
     *
     * @param Context $context
     * @param Synchronizer $synchronizer
     */
    public function __construct(
        Context $context,
        Synchronizer $synchronizer
    ) {
        parent::__construct($context);
        $this->synchronizer = $synchronizer;
    }

    /**
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->synchronizer->synchronizeData();
            
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData(['success' => true, 'time' => time()]);

            return $resultJson;

        } catch (\Exception $e) {
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData(['success' => false, 'time' => time()]);

            return $resultJson;
        }
    }

    /**
     * Returns result of current user permission check on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageWorx_CustomerPrices::config_mageworx_customerprices');
    }

}