<?php

namespace BoostMyShop\Supplier\Block\Product\Edit;

class Tab extends \Magento\Backend\Block\Widget\Tab
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        if (!$this->_request->getParam('id')) {
            $this->setCanShow(false);
        }
    }
}
