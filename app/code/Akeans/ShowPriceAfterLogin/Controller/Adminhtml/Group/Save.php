<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Akeans\ShowPriceAfterLogin\Controller\Adminhtml\Group;

use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;

class Save extends \Magento\Customer\Controller\Adminhtml\Group\Save
{
   

    /**
     * Create or save customer group.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $taxClass = (int)$this->getRequest()->getParam('tax_class');

        /** @var \Magento\Customer\Api\Data\GroupInterface $customerGroup */
        $customerGroup = null;
        if ($taxClass) {
            $id = $this->getRequest()->getParam('id');
            $resultRedirect = $this->resultRedirectFactory->create();
            try {
                $customerGroupCode = (string)$this->getRequest()->getParam('code');
                $order_prefix = (string)$this->getRequest()->getParam('order_prefix');
                if ($id !== null) {
                    $customerGroup = $this->groupRepository->getById((int)$id);
                    $customerGroupCode = $customerGroupCode ?: $customerGroup->getCode();
                } else {
                    $customerGroup = $this->groupDataFactory->create();
                }
                $customerGroup->setCode(!empty($customerGroupCode) ? $customerGroupCode : null);
                $customerGroup->setTaxClassId($taxClass);
                $customerGroup->setOrderPrefix($order_prefix);

                $this->groupRepository->save($customerGroup);
				echo '<pre>';print_r($customerGroup);die;
                $this->messageManager->addSuccess(__('You saved the customer group.'));
                $resultRedirect->setPath('customer/group');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                if ($customerGroup != null) {
                    $this->storeCustomerGroupDataToSession(
                        $this->dataObjectProcessor->buildOutputDataArray(
                            $customerGroup,
                            \Magento\Customer\Api\Data\GroupInterface::class
                        )
                    );
                }
                $resultRedirect->setPath('customer/group/edit', ['id' => $id]);
            }
            return $resultRedirect;
        } else {
            return $this->resultForwardFactory->create()->forward('new');
        }
    }
}
