<?php
/**
 * * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wanexo\Jumbo\Controller\Adminhtml\System\Config\Demo;

use Magento\Framework\Controller\Result\JsonFactory;

use Wanexo\Jumbo\Model\Stylesetting\Generator;


class Import extends \Wanexo\Jumbo\Controller\Adminhtml\System\Config\Demo
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    
  
    /**
     * @var Generator
     */
    protected $_cssGenerator;
    

     /**
     * Application config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_appConfig;

    
    

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory,Generator $cssenerator,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_cssGenerator= $cssenerator;
        $this->_appConfig = $config;
    }

    /**
     * Check whether vat is valid
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->_import();

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        
        
        
        $importResult=$resultJson->setData([
            'valid' => (int)$result->getIsValid(),
            'import_path' => $result->getImportPath(),
            'message' => $result->getRequestMessage(),
        ]);
        // re-init configuration
        $this->_appConfig->reinit();
        
       $this->_cssGenerator->generateCss('theme_style', null,null);
     
       return $importResult;
    }
}
