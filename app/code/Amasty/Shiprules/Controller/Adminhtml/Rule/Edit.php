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


class Edit extends \Amasty\Shiprules\Controller\Adminhtml\Rule
{
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Amasty\Shiprules\Model\Rule');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        } else {
            $this->_prepareForEdit($model);
        }
        $this->_coreRegistry->register('current_amasty_shiprules_rule', $model);
        $this->_initAction();
        if ($model->getId()) {
            $title = __('Edit Shipping Rule `%1`', $model->getName());
        } else {
            $title = __("Add new Shipping Rule");
        }
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_view->renderLayout();
    }

    /**
     * @param \Amasty\Shiprules\Model\Rule $model
     *
     * @return bool
     */
    protected function _prepareForEdit(\Amasty\Shiprules\Model\Rule $model)
    {
        foreach (parent::FIELDS as $field) {
            $val = $model->getData($field);

            if (!is_array($val)) {
                $model->setData($field, explode(',', $val));
            }
        }

        $model->getActions()->setJsFormObject('rule_conditions_fieldset');
        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');

        return true;
    }
}
