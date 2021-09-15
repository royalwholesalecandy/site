<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Block\Panel;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Main contact form block
 */
abstract class AbstractPanel extends Template
{
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
    protected $_helper;
	
    protected $_sectionFactory;
	
    protected $_section;
	
    protected $customerSession;
	
	protected $_attributeCollection;
	
	/**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;
	
	
	public function __construct(
		Template\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\MGS\Mpanel\Helper\Data $helper,
		CustomerSession $customerSession,
		\Magento\Cms\Model\Template\FilterProvider $filterProvider,
		\MGS\Mpanel\Model\ResourceModel\Section\CollectionFactory $sectionFactory,
		\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollection,
		array $data = []
	){
        parent::__construct($context, $data);
		$this->_objectManager = $objectManager;
		$this->_sectionFactory = $sectionFactory;
		$this->customerSession = $customerSession;
		$this->_filterProvider = $filterProvider;
		$this->_attributeCollection = $attributeCollection;
		$this->_helper = $helper;
    }
	
	public function getModel($model){
		return $this->_objectManager->create($model);
	}
	
	public function wasSave(){
		if($this->customerSession->getSaved()){
			return true;
		}
		return false;
	}
	
	public function unsetSaveSection(){
		$this->customerSession->setSaved(false);
		return ;
	}
	
	public function getHelper(){
		return $this->_helper;
	}
	
	public function getAvailableAttributes(){
		$attrs = [];
		
		$attributes = $this->_attributeCollection->create()->addVisibleFilter()
			->addFieldToFilter('backend_type', 'int')
			->addFieldToFilter('frontend_input', 'boolean');
		
		if(count($attributes)>0){
			foreach ($attributes as $productAttr) { 
				$attrs[$productAttr->getAttributeCode()] = $productAttr->getFrontendLabel();
			}
		}
		
        return $attrs;
	}
	
	public function getMegamenus(){
		$megamenu = $this->getModel('MGS\Mmegamenu\Model\Parents')
			->getCollection()
			->addFieldToFilter('parent_id', ['neq'=>1])
			->addFieldToFilter('status', 1);
		return $megamenu;
	}
}

