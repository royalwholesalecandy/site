<?php

namespace Wanexo\Brand\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;


class Brand extends AbstractDb
{
    
   protected function _construct()
    {
        $this->_init('wanexo_brand', 'brand_id');
    }
    
  }
  
?>