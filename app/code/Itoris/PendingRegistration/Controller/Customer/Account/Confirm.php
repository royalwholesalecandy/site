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

namespace Itoris\PendingRegistration\Controller\Customer\Account;

class Confirm extends \Magento\Customer\Controller\Account\Confirm
{
    protected $disableEmailFlag = false;

    public function execute(){
        /** @var $helper \Itoris\PendingRegistration\Helper\Data */
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\PendingRegistration\Helper\Data');
        $helper->init(null);
        $scope = $helper->getCustomerScope($this->session->getCustomer());
        if ($helper->isEnabled()
            && \Itoris\PendingRegistration\Model\Settings::inst()->isEngineActive($scope)
            && $helper->isCanSendEmail(\Itoris\PendingRegistration\Model\Template::$EMAIL_APPROVED, $scope)
        ) {
            $this->disableEmailFlag = true;
        }
        parent::execute();
    }

    protected function getSuccessRedirect(){
        if ($this->disableEmailFlag) {
            $successUrl = $this->urlModel->getUrl('*/*/index', ['_secure'=>true]);
            if ($this->session->getBeforeAuthUrl()) {
                $successUrl = $this->session->getBeforeAuthUrl(true);
            }
        } else {
            $successUrl = parent::getSuccessRedirect();
        }

        return $successUrl;
    }
}