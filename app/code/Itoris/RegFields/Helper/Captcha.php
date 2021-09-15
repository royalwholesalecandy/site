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

namespace Itoris\RegFields\Helper;

class Captcha extends Data {

    /**
     * Validate captcha code by captcha type
     *
     * @param $code
     * @param $captcha
     * @return bool
     */
    public function captchaValidate($code, $captcha) {
        $session = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Customer\Model\Session');

        switch($captcha){
            case 'securimage':
                    //require_once $this->getBaseDir() . "/app/code/Itoris/RegFields/Helper/Captcha/Securimage/Securimage.php";
                    /** @var \Itoris\RegFields\Helper\Captcha\Securimage\Securimage $img */
                    $img = $this->getObjectManager()->create('Itoris\RegFields\Helper\Captcha\Securimage\Securimage');
                    return $img->check($code);

            case 'alikon':
                    //require_once $this->getBaseDir() . "/app/code/Itoris/RegFields/Helper/Captcha/Alikon/Alikoncaptcha.php";
                    //return (strtolower($_SESSION['captcha_code']) == strtolower($code)) ? true : false;
                    return (strtolower($session->getCaptchaCode()) == strtolower($code)) ? true : false;

            case 'captchaform':
                    //require_once $this->getBaseDir() . "/app/code/Itoris/RegFields/Helper/Captcha/Captchaform/Captchaform.php";
                    //return (strtolower($_SESSION['captcha_code']) == strtolower($code)) ? true : false;
                    return (strtolower($session->getCaptchaCode()) == strtolower($code)) ? true : false;
        }
    }

    public function getBaseDir(){
        return $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
    }
}
