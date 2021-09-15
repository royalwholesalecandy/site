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

//app/code/Itoris/RegFields/Controller/Adminhtml/Form/Save.php
namespace Itoris\RegFields\Controller\Adminhtml\Form;

class Save extends \Itoris\RegFields\Controller\Adminhtml\Form
{
    const BLOCK_HTML_CACHE_TAG = 'BLOCK_HTML';

    public function execute(){
        $storeId = $this->getRequest()->getParam('store_id');
        $sections = $this->getRequest()->getParam('sections');
        $useDefault = $this->getRequest()->getParam('use_default');
        if($useDefault === null){
            $useDefault = "0";
        }

        try {
            if ($sections) {
                /** @var $formModel \Itoris\RegFields\Model\Form */
                $formModel = $this->_objectManager->create('Itoris\RegFields\Model\Form');
                $formModel->load($storeId, 'view_id');
                $formModel->setViewId($storeId);
                $formModel->setConfig(serialize($sections));
                $formModel->setUseDefault($useDefault);
                $formModel->save();
                //Clear cache
                /** @var \Magento\Framework\App\Cache\Frontend\Pool  $cacheFrontendPool */
                $cacheFrontendPool = $this->_objectManager->create('Magento\Framework\App\Cache\Frontend\Pool');
                /** @var \Magento\Framework\Cache\FrontendInterface $cacheFrontend */
                foreach($cacheFrontendPool as $cacheFrontend){
                    $cacheFrontend->getBackend()->clean(\Zend_Cache::CLEANING_MODE_ALL, self::BLOCK_HTML_CACHE_TAG);
                }
            }
        } catch (\Exception $e) {
            $this->getMessageManager()->addError(__($e->getMessage()));
            $this->getMessageManager()->addWarning(__('Settings have not been saved'));
        }
        $this->getMessageManager()->addSuccess(__('Settings have been saved'));
        $this->_redirect($this->_getSession()->getBeforUrl());
    }
}