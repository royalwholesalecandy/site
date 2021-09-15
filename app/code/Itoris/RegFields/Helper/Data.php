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

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig */
    protected $_scopeConfig;
    /** @var \Magento\Framework\ObjectManagerInterface $_objectManager */
    protected $_objectManager;
    /** @var \Magento\Framework\Message\ManagerInterface $messageManager */
    protected $messageManager;
    protected $_mediaDirectory;
    /** @var \Magento\Framework\Filesystem $filesystem */
    protected $filesystem;
    /** @var  \Magento\Backend\App\ConfigInterface $_backendConfig */
    protected $_backendConfig;

    public $configTableName = 'core_config_data';

    const XML_PATH_MODULE_ENABLED = 'itoris_regfields/general/enabled';
    const XML_PATH_CORE = 'itoris_core/installed/';
    const MODULE_NAME = 'Itoris_RegFields';
    const XML_PATH_DISABLE_MODULES = 'advanced/modules_disable_output/';
    const SCOPE_TYPE_STORES = 'stores';

    const ENABLED = 1;
    const DISABLED = 2;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        $data = []
    ){
        $this->messageManager = $messageManager;
        $this->_objectManager = $objectManager;
        /** @var \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig */
        $this->_scopeConfig = $this->getObjectManager()->create('Magento\Framework\App\Config\ScopeConfigInterface');
        /** @var \Magento\Framework\Message\ManagerInterface messageManager */
        $this->messageManager = $this->getObjectManager()->create('Magento\Framework\Message\ManagerInterface');
        /** @var \Magento\Framework\Filesystem $filesystem */
        $this->filesystem = $this->getObjectManager()->create('Magento\Framework\Filesystem');
        $this->_mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_backendConfig = $this->_objectManager->create('Magento\Backend\App\ConfigInterface');
        parent::__construct($context);
    }

    protected $alias = 'registration_fields';

    public function isDisabledForStore($storeId = null){
        if($storeId == null){
            $storeId = $this->getStoreManager()->getStore()->getId();
        }
        return !(bool)$this->_scopeConfig->getValue(self::XML_PATH_MODULE_ENABLED, self::SCOPE_TYPE_STORES, $storeId);
    }

    public function isEnabled(){
        return /*(int)$this->_backendConfig->getValue(self::XML_PATH_MODULE_ENABLED) &&*/ !$this->isDisabledForStore()
        && count(explode('|', $this->_backendConfig->getValue('itoris_core/installed/Itoris_RegFields'))) == 2;
    }

    public function getScopeConfig($path, $type = self::SCOPE_TYPE_STORES, $storeId = null){
        if($storeId == null){
            $storeId = $this->getStoreManager()->getStore()->getId();
        }
        return $this->_scopeConfig->getValue($path, $type, $storeId);
    }

    public function getAlias() {
        return $this->alias;
    }

    /**
     * Upload file by file id in $_FILES
     *
     * @param $fileId
     * @return array
     */
    public function uploadFiles($fileId) {
        try {
            /** @var \Magento\Framework\File\Uploader $uploader */
            $uploader = new \Magento\Framework\File\Uploader($fileId);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $dir = $this->_mediaDirectory->getAbsolutePath(). DIRECTORY_SEPARATOR . 'customer';
            if (!is_dir($dir)) {
                mkdir($dir);
            }
            $dir .=  DIRECTORY_SEPARATOR . 'itoris';
            if (!is_dir($dir)) {
                mkdir($dir);
            }
            $result = $uploader->save($dir);

            $result['url'] = $this->_mediaDirectory->getAbsolutePath() . 'customer/itoris/' . $result['file'];

        } catch (\Exception $e) {
            $result = [
                'error' => __('Cannot upload file.'),
            ];
        }
        return $result;
    }

    /**
     * Get file from /media/customer/itoris/ by filename
     *
     * @param $fileName
     */
    public function getFile($fileName) {
        $filePath = $this->_mediaDirectory->getAbsolutePath() . DIRECTORY_SEPARATOR . 'customer' . DIRECTORY_SEPARATOR . 'itoris' . $fileName;
        @ob_clean(); // clear output buffer
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // some day in the past
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Content-type: application/x-download");
        header("Content-Transfer-Encoding: binary");
        header("Accept-Ranges: bytes");
        header("Content-Length: ".filesize($filePath));
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        readfile($filePath);
        //exit;
    }

    public function isAjaxLoginActive(){
        if($this->_moduleManager->isEnabled('Itoris_AjaxLogin')){
            /** @var $ajaxLoginHelper \Itoris\AjaxLogin\Helper\Data */
            $ajaxLoginHelper = $this->getAjaxLoginHelper();
            return $ajaxLoginHelper->isEnabled();
        }
    }
    /**
     * @return \Itoris\AjaxLogin\Helper\Data
     */
    public function getAjaxLoginHelper(){
        return $this->_objectManager->create('Itoris\AjaxLogin\Helper\Data');
    }
    /**
     * @return \Magento\Framework\Escaper
     */
    public function getEscaper(){
        return $this->getObjectManager()->create('Magento\Framework\Escaper');
    }

    public function htmlEscape($data, $allowedTags = null){
        return $this->getEscaper()->escapeHtml($data, $allowedTags);
    }
    /**
     * @return \Itoris\RegFields\Helper\Mime
     */
    protected function getMimeHelper() {
        return $this->getObjectManager()->create('Itoris\RegFields\Helper\Mime');
    }
    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager(){
        return $this->_objectManager;
    }
    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager(){
        return $this->getObjectManager()->create('Magento\Store\Model\StoreManagerInterface');
    }
    /**
     * @return \Magento\Framework\UrlInterface
     */
    public function getUrlModel(){
        return $this->getObjectManager()->create('Magento\Framework\UrlInterface');
    }

    /**
     * @return \Magento\Framework\Filesystem|mixed
     */
    public function getFilesystem(){
        return $this->filesystem;
    }

    /**
     * @return \Magento\Framework\Message\ManagerInterface
     */
    public function getMessageManager(){
        return $this->messageManager;
    }

    /**
     * @return \Itoris\RegFields\Helper\Field
     */
    public function getFieldHelper(){
        return $this->getObjectManager()->create('Itoris\RegFields\Helper\Field');
    }
    /**
     * Create connection adapter instance
     * @return \Magento\Framework\App\ResourceConnection
     */
    public function getResourceConnection(){
        return $this->getObjectManager()->create('Magento\Framework\App\ResourceConnection');
    }

    /**
     * @return \Magento\Framework\View\LayoutFactory
     */
    public function getLayoutFactory(){
        return $this->getObjectManager()->create('Magento\Framework\View\LayoutFactory');
    }

    /**
     * @return \Magento\Backend\App\ConfigInterface|mixed
     */
    public function getBackendConfig(){
        return $this->_backendConfig;
    }
}
