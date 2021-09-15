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


class Duplicate extends \Amasty\Shiprules\Controller\Adminhtml\Rule
{
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('rule_id');
        if (!$id) {
            $this->messageManager->addErrorMessage(__('Please select a rule to duplicate.'));
            return $this->_redirect('*/*');
        }
        try {
            $model = $this->_objectManager->create('Amasty\Shiprules\Model\Rule')->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
                $this->_redirect('*/*');
                return;
            }

            $rule = clone $model;
            $rule->setIsActive(0);
            $rule->setId(null);
            $rule->save();

            $session = $this->_objectManager->get('Magento\Backend\Model\Session');
            $this->messageManager->addSuccessMessage(__('The rule has been duplicated. Please feel free to activate it.'));

            return $this->_redirect('*/*/edit', array('id' => $rule->getId()));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('*/*');
            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the item data. Please review the error log.')
            );
            $this->logger->critical($e);
            $this->_redirect('*/*');
            return;
        }
    }
}