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
 * @package    ITORIS_M2_REGISTRATION_FIELDS_MANAGER
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\RegFields\Block\Adminhtml\Config;

class Form extends \Magento\Backend\Block\Widget\Form\Container
{
    public function _construct() {
        parent::_construct();
        $this->_blockGroup = 'Itoris_RegFields';
        $this->_controller = 'adminhtml_config';
        $this->updateButton('save', 'label', $this->escapeHtml(__('Save Form')));
        $this->removeButton('delete');
        $this->removeButton('back');
        $this->removeButton('reset');
    }

    public function getHeaderText() {
        return $this->escapeHtml(__('Registration Fields Manager'));
    }
}