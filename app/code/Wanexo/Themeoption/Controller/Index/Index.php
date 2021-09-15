<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wanexo\Themeoption\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * Renders CMS Home page
     *
     * @param string|null $coreRoute
     * @return \Magento\Framework\Controller\Result\Forward
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($coreRoute = null)
    {
        $pageId = $this->_objectManager->get(
            'Magento\Framework\App\Config\ScopeConfigInterface'
        )->getValue(
            \Magento\Cms\Helper\Page::XML_PATH_HOME_PAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $resultPage = $this->_objectManager->get('Magento\Cms\Helper\Page')->prepareResultPage($this, $pageId);
        if (!$resultPage) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('defaultIndex');
            return $resultForward;
        }
            //add custom class on body tag
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $config = $objectManager->create('Wanexo\Themeoption\Helper\Data')->getConfig('wanexo_themeoption/wxo_class_settings/enable');
            $homePage = $objectManager->create('Wanexo\Themeoption\Helper\Data')->getConfig('wanexo_themeoption/wxo_class_settings/home_page');
            $homePageClass = $objectManager->create('Wanexo\Themeoption\Helper\Data')->getConfig('wanexo_themeoption/wxo_class_settings/home_page_class');
            if($config){
                if($homePage && strlen($homePageClass)>0){
                 $resultPage->getConfig()->addBodyClass($homePageClass); 
                }
            }
        
        return $resultPage;
    }
}
