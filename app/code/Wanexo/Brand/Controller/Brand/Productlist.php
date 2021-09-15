<?php

namespace Wanexo\Brand\Controller\Brand;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;


 class Productlist extends Action
{
 
  protected $scopeConfig;
  
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ScopeConfigInterface $scopeConfig
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
    }
    
    public function execute()
    {
      
      $id = $this->getRequest()->getParam("id");
      
      /*get option text from option id*/
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('Magento\Catalog\Model\Product');
	     $attr = $model->getResource()->getAttribute("is_brand");
         $option_text = $attr->getSource()->getOptionText($id);
        
    
        $resultPage = $this->resultPageFactory->create();
        //$resultPage->getConfig()->getTitle()->set(__('Brand Extension'));
        $breadcrumbsBlock = $resultPage->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbsBlock) {
                $breadcrumbsBlock->addCrumb(
                    'home',
                    [
                        'label'    => __('Home'),
                        'link'     => $this->_url->getUrl('')
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'brand',
                    [
                        'label'    => __($option_text),
                    ]
                );
              
            }
  
        return $resultPage;
    }
  
 }
 
 ?>