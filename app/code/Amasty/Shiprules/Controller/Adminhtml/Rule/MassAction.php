<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Shiprules\Controller\Adminhtml\Rule;


class MassAction extends \Amasty\Shiprules\Controller\Adminhtml\Rule
{
    public function execute()
    {

        $ids = $this->getRequest()->getParam('rules');
        $action = $this->getRequest()->getParam('action');
        if ($ids && in_array($action, ['activate', 'inactivate', 'delete'])) {
            try {
                $status = -1;
                switch($action) {
                    case 'delete':
                        /**
                         * @var $collection \Amasty\Shiprules\Model\ResourceModel\Rule\Collection
                         */
                        $collection = $this->_objectManager->create('Amasty\Shiprules\Model\ResourceModel\Rule\Collection');

                        $collection->addFieldToFilter('rule_id', array('in'=>$ids));
                        $collection->walk($action);
                        $status = -1;
                        $message = __('You deleted the rule(s).');
                        break;
                    case 'activate':
                        $status = 1;
                        $message = __('You activated the rule(s).');
                        break;
                    case 'inactivate':
                        $status = 0;
                        $message = __('You deactivated the rule(s).');
                        break;
                }

                if($status > -1) {
                    $this->_objectManager->create('Amasty\Shiprules\Model\Rule')->massChangeStatus($ids, $status);
                }

                $this->messageManager->addSuccessMessage($message);
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete/activate/deactivate rule(s) right now. Please review the log and try again.').$e->getMessage()
                );
                $this->logger->critical($e);
                $this->_redirect('*/*/');
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a rule(s) to delete/activate/deactivate.'));
        $this->_redirect('*/*/');
    }
}
