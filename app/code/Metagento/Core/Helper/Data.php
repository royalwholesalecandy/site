<?php

namespace Metagento\Core\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(\Magento\Framework\App\Helper\Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path);
    }




}