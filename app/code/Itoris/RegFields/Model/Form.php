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

namespace Itoris\RegFields\Model;
use Magento\Framework\DataObject\IdentityInterface;

class Form extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'regfields_form_tag';

    private $defaultSections = null;

    public function _construct() {
        $this->_init('Itoris\RegFields\Model\ResourceModel\Form');
        $pageConfig = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\View\Page\Config');
        $pageConfig->getTitle()->set(__('Create an Account'));
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getSectionsJson($viewId) {
        $config = $this->getFormConfig($viewId);
        return \Zend_Json::encode($config);
    }

    public function getFormConfig($viewId) {
        $this->load($viewId, 'view_id');
        if($this->getData('use_default') == 'on' || $this->getId() == null){
            $viewId = 0;
            $config = $this->load($viewId, 'view_id')->getConfig();
        } else{
            $config = $this->getConfig();
        }
        if ($config) {
            $config = unserialize($config);
            $config = array_values($config);
            for ($i = 0; $i < count($config); $i++) {
                if (isset($config[$i]['fields'])) {
                    $config[$i]['fields'] = array_values($config[$i]['fields']);
                    for ($j = 0; $j < count($config[$i]['fields']); $j++) {
                        if (isset($config[$i]['fields'][$j]['items'])) {
                            $config[$i]['fields'][$j]['items'] = array_values($config[$i]['fields'][$j]['items']);
                        }
                    }
                }
            }
        } else {
            $config = $this->getFieldHelper()->getDefaultSections();
        }
        return $config;
    }

    public function getDefaultSectionsJson() {
        $config = $this->getFieldHelper()->getDefaultSections();
        return \Zend_Json::encode($config);
    }

    /**
     * @return \Itoris\RegFields\Helper\Field
     */
    private function getFieldHelper() {
        return \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\RegFields\Helper\Field');
    }
}