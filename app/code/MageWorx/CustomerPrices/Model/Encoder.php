<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Model;

use \Magento\Framework\Translate\InlineInterface;

class Encoder
{
    /**
     * @var InlineInterface
     */
    protected $translateInlineData;

    /**
     * @param InlineInterface $translateInlineData
     */
    public function __construct(InlineInterface $translateInlineData)
    {
        $this->translateInlineData = $translateInlineData;
    }

    /**
     * Encode data
     *
     * @param mixed $data
     * @return string
     */
    public function encode($data)
    {
        $this->translateInlineData->processResponseBody($data);
        return \Zend_Json::encode($data);
    }
}