<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Controller\Adminhtml\Segment;

use Magento\Rule\Model\Condition\AbstractCondition;

class NewConditionHtml extends \Amasty\Segments\Controller\Adminhtml\Segment
{
    /**
     * @return void
     */
    public function execute()
    {
        $segmentId = $this->getRequest()->getParam(self::CONDITION_PARAM_URL_KEY);
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        try {
            $model = $this->_objectManager
                ->create($type)
                ->setId($segmentId)
                ->setType($type)
                ->setRule($this->salesRuleFactory->create())
                ->setPrefix('conditions')
                ->setFormName($this->getRequest()->getParam('form_namespace'));

            if (!empty($typeArr[1])) {
                $model->setAttribute($typeArr[1]);
            }

            if ($model instanceof AbstractCondition) {
                $model->setJsFormObject($this->getRequest()->getParam('form'));
                $html = $model->asHtmlRecursive();
            } else {
                $html = '';
            }
        } catch (\Exception $exception) {
            $html = (string)$exception;
        }

        $this->getResponse()->setBody($html);
    }
}
