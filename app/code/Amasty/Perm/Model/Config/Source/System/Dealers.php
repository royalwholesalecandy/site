<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Model\Config\Source\System;

class Dealers extends \Amasty\Perm\Model\Config\Source\Dealers
{
    public function toArray($withAdmin = false, array $allowedDealers = [])
    {
        $ret = ['0' => __('No')];
        foreach(parent::toArray($withAdmin, $allowedDealers) as $key => $val){
            $ret[$key] = $val;
        }
        return $ret;
    }
}