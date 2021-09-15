<?php

namespace BoostMyShop\UltimateReport\Model\ResourceModel;


class Report extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('', '');
    }


    public function runQuery($sql, $max = null)
    {
        if ($max)
            $sql .= " limit 0,".$max;

        return $this->getConnection()->fetchAll($sql);
    }

    public function getDbConnection()
    {
        return $this->getConnection();
    }

    public function getTableName($tableName)
    {
        return $this->getTable($tableName);
    }


}
