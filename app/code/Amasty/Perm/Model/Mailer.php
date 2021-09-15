<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Model;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\Store;
use Magento\Backend\App\Area\FrontNameResolver;
use Amasty\Perm\Helper\Data as PermHelper;

class Mailer {
    const SCOPE_MESSAGES_ENABLED = 'amasty_perm/messages/enabled';
    const SCOPE_MESSAGES_IDENTIFY = 'amasty_perm/messages/identity';
    const SCOPE_MESSAGES_TEMPLATE = 'amasty_perm/messages/template';
    const SCOPE_MESSAGES_ADMIN_NAME = 'amasty_perm/messages/admin_name';
    const SCOPE_MESSAGES_ADMIN_EMAIL = 'amasty_perm/messages/admin_email';
    const SCOPE_MESSAGES_SEE_OTHER_DEALERS = 'amasty_perm/messages/see_other_dealers';



    protected $_transportBuilder;

    public function __construct(
        TransportBuilder $transportBuilder,
        PermHelper $permHelper
    ){
        $this->_transportBuilder = $transportBuilder;
        $this->_permHelper = $permHelper;
    }

    public function send($storeId, array $emailsList, array $vars)
    {
        if ($this->_permHelper->getScopeValue(self::SCOPE_MESSAGES_ENABLED) === '1' &&
            count($emailsList) > 0){

            $this->_transportBuilder
                ->setTemplateIdentifier($this->_permHelper->getScopeValue(self::SCOPE_MESSAGES_TEMPLATE))
                ->setTemplateOptions(
                    [
                        'area' => FrontNameResolver::AREA_CODE,
                        'store' => $storeId
                    ]
                )->setTemplateVars($vars)
                    ->setFrom($this->_permHelper->getScopeValue(self::SCOPE_MESSAGES_IDENTIFY));

            foreach($emailsList as $emailsList){
                $this->_transportBuilder->addTo($emailsList['email'], $emailsList['name']);
            }

            $transport = $this->_transportBuilder->getTransport();

            $transport->sendMessage();
        }
    }
}