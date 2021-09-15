<?php

namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate\Extract;

abstract class ExtractAbstract
{
    abstract function extract($data, $carrierTemplate);
}