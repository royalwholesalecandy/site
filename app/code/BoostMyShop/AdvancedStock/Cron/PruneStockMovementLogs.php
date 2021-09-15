<?php namespace BoostMyShop\AdvancedStock\Cron;


class PruneStockMovementLogs {

    protected $_model;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\StockMovementLogs $model
    ){
        $this->_model = $model;
    }

    public function execute(){

        $this->_model->prune();

    }

}