<?php

namespace BoostMyShop\UltimateReport\Model\Report\Source;

abstract class AbstractSource
{

    public abstract function getReportDatas($max = null);

}