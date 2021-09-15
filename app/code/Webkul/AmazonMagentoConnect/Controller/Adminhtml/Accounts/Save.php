<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Controller\Adminhtml\Accounts;

use Magento\Framework\Locale\Resolver;
use Webkul\AmazonMagentoConnect\Model\AccountsFactory;
use Webkul\AmazonMagentoConnect\Controller\Adminhtml\Accounts;

class Save extends Accounts
{
     /**
      * @var \Magento\Framework\Controller\Result\JsonFactory
      */
    private $resultJsonFactory;

    /**
     * @var \Webkul\AmazonMagentoConnect\Helper\Data
     */
    private $dataHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        AccountsFactory $accountsFactory,
        \Webkul\AmazonMagentoConnect\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->accountsFactory = $accountsFactory;
        $this->dataHelper =  $helper;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('entity_id');
        $data = $this->getRequest()->getParams();

        if (!$data) {
            $this->_redirect('amazonmagentoconnect/*/');
            return;
        }
        $amzClient = $this->dataHelper->validateAmzCredentials($data);
        if ($amzClient) {
            $sellerParticipation = $amzClient->ListMarketplaceParticipations();
            $participateMp = $sellerParticipation['ListMarketplaces']['Marketplace'];
            $participateMp = isset($participateMp[0]) ? $participateMp : [0 => $participateMp];
            foreach ($participateMp as $marketplace) {
                if ($marketplace['MarketplaceId'] === $data['marketplace_id']) {
                    $data['currency_code'] =  $marketplace['DefaultCurrencyCode'];
                    $data['country'] = $marketplace['DefaultCountryCode'];
                }
            }
            $model = $this->accountsFactory->create()->load($id);

            if ($id && $model->isObjectNew()) {
                $this->messageManager->addError(__('This account no longer exists.'));
                $this->_redirect('amazonmagentoconnect/*/');
                return;
            }

            try {
                $amzCollection = $this->accountsFactory->create()->getCollection();
                $amzCollection->addFieldToFilter('store_name', $data['store_name']);
                if ($amzCollection->getSize() && !$id) {
                    $this->messageManager->addError(__('Store Name Already Taken'));
                    $this->_redirect('amazonmagentoconnect/*/');
                    return;
                }
                if (isset($data['created_at'])) {
                    unset($data['created_at']);
                }

                $id = $model->setData($data)->save()->getId();
                $this->messageManager->addSuccess(__('You saved the amazon seller account detail.'));
            } catch (\Exception $e) {
                $this->messageManager->addMessages(__('something went wrong'));
                $this->_redirect('amazonmagentoconnect/*/');
            }
            $this->redirectToEdit($data, $id);
        } else {
            $this->messageManager->addError(__('Amazon account details are not correct'));
            $this->_redirect('amazonmagentoconnect/*/');
        }
    }

    /**
     * @param \Magento\User\Model\User $model
     * @param array $data
     * @return void
     */
    private function redirectToEdit(array $data, $id)
    {
        $this->_getSession()->setAmzAccountData($data);
        $data['entity_id'] = $id;
        $arguments = $data['entity_id'] ? ['id' => $data['entity_id']]: [];
        $arguments = array_merge(
            $arguments,
            ['_current' => true, 'active_tab' => $data['active_tab']]
        );
        if (isset($data['entity_id']) && isset($data['back'])) {
            $this->_redirect('amazonmagentoconnect/*/edit', $arguments);
        } else {
            $this->_redirect('amazonmagentoconnect/*/index', $arguments);
        }
    }
}
