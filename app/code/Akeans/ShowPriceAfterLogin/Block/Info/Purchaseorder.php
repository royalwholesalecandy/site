<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Akeans\ShowPriceAfterLogin\Block\Info;

class Purchaseorder extends \Magento\OfflinePayments\Block\Info\Purchaseorder
{
    /**
     * @var string
     */
    protected $_template = 'Akeans_ShowPriceAfterLogin::info/purchaseorder.phtml';

    /**
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('Akeans_ShowPriceAfterLogin::info/pdf/purchaseorder.phtml');
        return $this->toHtml();
    }
}
