<?php
/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* =================================================================
*                 MAGENTO EDITION USAGE NOTICE
* =================================================================
* This package designed for Magento COMMUNITY edition
* BSS Commerce does not guarantee correct work of this extension
* on any other Magento edition except Magento COMMUNITY edition.
* BSS Commerce does not provide extension support in case of
* incorrect edition usage.
* =================================================================
*
* @category   BSS
* @package    Bss_Paymentshipping
* @author     Extension Team
* @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
namespace Bss\Paymentshipping\Model;

/**
 * Paymentshipping Config model
 */
class Config extends \Magento\Framework\DataObject
{

	/**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
	/**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface 
     */
    protected $scopeConfig;
	/**
     * @var \Magento\Framework\App\Config\ValueInterface
     */
    protected $backendModel;
	/**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;
	/**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $configValueFactory;

	/**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager,
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
     * @param \Magento\Framework\App\Config\ValueInterface $backendModel,
     * @param \Magento\Framework\DB\Transaction $transaction,
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory,
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\ValueInterface $backendModel,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->backendModel = $backendModel;
        $this->transaction = $transaction;
        $this->configValueFactory = $configValueFactory;
	}

    protected function getStoreId() {
        return (int)$this->storeManager->getStore()->getId();
    }

    protected function getStoreCode() {
        return $this->storeManager->getStore()->getCode();
    }
	
	/**
	 * Function for getting Config value of current store
     * @param string $path,
     */
	public function getCurrentStoreConfigValue($path){
		return $this->scopeConfig->getValue($path,'store',$this->getStoreCode());
	}
	
	/**
	 * Function for setting Config value of current store
     * @param string $path,
	 * @param string $value,
     */
	public function setCurrentStoreConfigValue($path,$value){
		$data = [
                    'path' => $path,
                    'scope' =>  'stores',
                    'scope_id' => $this->getStoreId(),
                    'scope_code' => $this->storeCode,
                    'value' => $value,
                ];

		$this->backendModel->addData($data);
		$this->transaction->addObject($this->backendModel);
		$this->transaction->save();
	}
	
}
