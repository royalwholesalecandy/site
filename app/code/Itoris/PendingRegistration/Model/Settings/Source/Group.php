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

class Group extends \Itoris\PendingRegistration\Model\Settings implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;
    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $groups = $this->getGroupManager()->getLoggedInGroups();
            $this->_options = $this->getConverter()->toOptionArray($groups, 'id', 'code');
            array_unshift($this->_options, ['value' => '', 'label' => __('All Groups')]);
        }
        return $this->_options;
    }

    /**
     * @return \Magento\Customer\Api\GroupManagementInterface
     */
    protected function getGroupManager(){
        return $this->_objectManager->create('Magento\Customer\Api\GroupManagementInterface');
    }
    /**
     * @return \Magento\Framework\Convert\DataObject
     */
    protected function getConverter(){
        return $this->_objectManager->create('Magento\Framework\Convert\DataObject');
    }
}
