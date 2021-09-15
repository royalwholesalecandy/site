<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\CustomShippingPrice\Plugin\Sales\Model\AdminOrder;

use Magento\Sales\Model\AdminOrder\Create as OrderCreate;

class Create
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_session;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;
    
    /**
     * Add constructor.
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Locale\FormatInterface $localeFormat
    ) {
        $this->_session = $authSession;
        $this->_localeFormat = $localeFormat;
    }

    /**
     * @param OrderCreate $subject
     * @return mixed
     */
    public function afterImportPostData(OrderCreate $subject, $result)
    {
        $data = $subject->getData();

        if (isset($data['shipping_amount'])) {
            $shippingPrice = $this->_parseShippingPrice(
                $data['shipping_amount']
            );
            $this->_session->setCustomshippriceAmount($shippingPrice);
        }

        if (isset($data['shipping_description'])) {
            $this->_session->setCustomshippriceDescription(
                $data['shipping_description']
            );
        }
       
        return $result;
    }

    protected function _parseShippingPrice($price)
    {
        $price = $this->_localeFormat->getNumber($price);
        $price = $price>0 ? $price : 0;
        return $price;
    }
}
