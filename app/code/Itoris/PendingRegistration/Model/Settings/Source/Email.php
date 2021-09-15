<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PENDING_REGISTRATION
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */
namespace Itoris\PendingRegistration\Model\Settings\Source;

class Email extends \Itoris\PendingRegistration\Model\Settings implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        /** @var $collection \Magento\Email\Model\ResourceModel\Template\Collection */
        if (!($collection = $this->_registry->registry('config_system_email_template'))) {
            $collection = $this->getTemplateFactory()->create();
            $collection->load();
            $this->_registry->register('config_system_email_template', $collection);
        }
        $options = $collection->toOptionArray();
        $templateId = str_replace('/', '_', $this->getPath());
        $templateLabel = $this->getEmailConfig()->getTemplateLabel($templateId);
        $templateLabel = __('%1 (Default)', $templateLabel);
        array_unshift($options, ['value' => $templateId, 'label' => $templateLabel]);
        array_push($options, ['value' => 'disable', 'label' => __('Disable')]);
        return $options;
    }

    /**
     * @return \Magento\Email\Model\Template\Config
     */
    protected function getEmailConfig(){
        return $this->_objectManager->create('Magento\Email\Model\Template\Config');
    }

    /**
     * @return \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    protected function getTemplateFactory(){
        return $this->_objectManager->create('Magento\Email\Model\ResourceModel\Template\CollectionFactory');
    }
}