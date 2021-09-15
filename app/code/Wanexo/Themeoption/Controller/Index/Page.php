<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wanexo\Themeoption\Controller\Index;

class Page extends \Magento\Framework\View\Result\Page
{
   protected function addDefaultBodyClasses()
   {
        $this->pageConfig->addBodyClass($this->request->getFullActionName('-'));
        $pageLayout = $this->getPageLayout();
        if ($pageLayout) {
            $this->pageConfig->addBodyClass('page-layout-' . $pageLayout);
            // add custom class on body for all page
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $config = $objectManager->create('Wanexo\Themeoption\Helper\Data')->getConfig('wanexo_themeoption/wxo_class_settings/enable');
            $allPage = $objectManager->create('Wanexo\Themeoption\Helper\Data')->getConfig('wanexo_themeoption/wxo_class_settings/all_pages');
            $allPageClass = $objectManager->create('Wanexo\Themeoption\Helper\Data')->getConfig('wanexo_themeoption/wxo_class_settings/all_pages_class');
			
			$headerTypeClass = $objectManager->create('Wanexo\Themeoption\Helper\Data')->getConfig('wanexo_themeoption/wxo_header_setting/wxo_header');
			
            if($headerTypeClass){
               $this->pageConfig->addBodyClass('header'.$headerTypeClass); 
            }
			
			$headingType = $objectManager->create('Wanexo\Themeoption\Helper\Data')->getConfig('wanexo_themeoption/genral_setting/wxo_heading');
            if($headingType){
               $this->pageConfig->addBodyClass('heading'.$headingType); 
            }
			
			$rtlsupport = $objectManager->create('Wanexo\Themeoption\Helper\Data')->getConfig('wanexo_themeoption/genral_setting/rtl');
            if($rtlsupport){
               $this->pageConfig->addBodyClass('rtl'); 
            }
			
			$boxedTheme = $objectManager->create('Wanexo\Themeoption\Helper\Data')->getConfig('wanexo_themeoption/genral_setting/boxed');
            if($boxedTheme){
               $this->pageConfig->addBodyClass('boxed'); 
            }
			

			
			
            if($config){
                if($allPage && strlen($allPageClass)>0){
                 $this->pageConfig->addBodyClass($allPageClass); 
                }
            }
        }
        return $this;
   }
}