<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Plugin;

use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Amasty\Perm\Helper\Data as PermHelper;

class OrderEmailContainerOrderIdentity
{
    protected $_permHelper;
    public function __construct(
        PermHelper $permHelper
    ){
        $this->_permHelper = $permHelper;
    }
    public function afterGetEmailCopyTo(
        OrderIdentity $orderIdentity,
        $data
    ){
        if ($this->_permHelper->isSendEmailMode() && $this->_permHelper->hasDealers())
        {
            if (!is_array($data)){
                $data = [];
            }

            foreach ($this->_permHelper->getDealers() as $dealer){
                $emails = $dealer->getAllEmails();
                if (is_array($emails) && count($emails) > 0){
                    $data = array_merge($data, $emails);
                }
            }
        }

        return $data;
    }
}