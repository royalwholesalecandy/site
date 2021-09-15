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


class Save extends \Amasty\Shiprules\Controller\Adminhtml\Rule
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->_objectManager->create('Amasty\Shiprules\Model\Rule');
                $data = $this->getRequest()->getPostValue();
                $inputFilter = new \Zend_Filter_Input(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('rule_id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
                    }
                }
                if (isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                }
                if (isset($data['rule']['actions'])) {
                    $data['actions'] = $data['rule']['actions'];
                }
                unset($data['rule']);

                $model->addData($data);
                $model->loadPost($data); // rules
                $this->_prepareForSave($model);

                $this->_session->setPageData($model->getData());
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the rule.'));
                $this->_session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int)$this->getRequest()->getParam('rule_id');
                if (!empty($id)) {
                    $this->_redirect('*/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('*/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->_session->setPageData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('rule_id')]);
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * @param \Amasty\Shiprules\Model\Rule $model
     *
     * @return bool
     */
    protected function _prepareForSave(\Amasty\Shiprules\Model\Rule $model)
    {
        foreach (parent::FIELDS as $field){
            // convert data from array to string
            $val = $model->getData($field);
            $model->setData($field, '');

            if (is_array($val)) {
                $model->setData($field, implode(',', $val));
            }
        }

        return true;
    }
}
