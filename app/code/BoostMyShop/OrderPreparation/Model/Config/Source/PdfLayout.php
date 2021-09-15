<?php

namespace BoostMyShop\OrderPreparation\Model\Config\Source;

class PdfLayout implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return ['small' => 'Small font', 'large' => 'Large font with images'];
    }

}
