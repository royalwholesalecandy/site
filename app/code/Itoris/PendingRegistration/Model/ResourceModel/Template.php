<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PENDING_REGISTRATION
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\PendingRegistration\Model\ResourceModel;

class Template extends \Itoris\PendingRegistration\Model\ResourceModel\ScopedModel
{
    protected function _construct()
    {
        $this->_init('itoris_pendingregistration_templates', 'id');
    }


    public function loadByTypeScope(\Itoris\PendingRegistration\Model\Template $inst, $type, array $scope){

        $read = $this->getConnection();

        $scopeWhere = [];
        foreach($scope as $scopeType => $scopeArea){
            if($scopeArea !== null){
                $scopeWhere[] = "(scope={$read->quote($scopeType)} and scope_area=:$scopeType)";
            }
        }
        $scopeWhere = implode($scopeWhere, ' or ');
        
        $select = $this->getConnection()->select()
                ->from($this->getMainTable())
                ->where('`type`=:type')
                ->where($scopeWhere)
                ->order(new \Zend_Db_Expr("field(scope, 'store', 'website', 'default') asc"))
                ->limit(1);
        
        $data = $read->fetchRow($select, array_merge( $scope, ['type' => $type]));

        if ($data) {
            $inst->setData($data);
        }

        $this->unserializeFields($inst);
        $this->_afterLoad($inst);

        return $this;
    }
}
