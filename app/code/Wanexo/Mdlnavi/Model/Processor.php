<?php
    namespace Wanexo\Mdlnavi\Model;

    class Processor 
    {
        public function __construct(
            \Magento\Cms\Model\Template\FilterProvider $filterProvider,
            \Magento\Cms\Model\BlockFactory $blockFactory
        ) {
            $this->_filterProvider = $filterProvider;
            $this->_blockFactory = $blockFactory;
        }
    
        public function content($content)
        {
            return $this->_filterProvider->getBlockFilter()->filter($content);
        }  
    }
?>