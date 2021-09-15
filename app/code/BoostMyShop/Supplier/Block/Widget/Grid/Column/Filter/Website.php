<?php

namespace BoostMyShop\Supplier\Block\Widget\Grid\Column\Filter;


class Website extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    protected $_websiteCollectionFactory;
    protected $_productCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    ){
        parent::__construct($context, $resourceHelper, $data);

        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    protected function _getOptions()
    {
        $options = [];
        $options[] = ['value' => null, 'label' => ''];

        $collection = $this->_websiteCollectionFactory->create();

        foreach($collection as $website)
        {
            $options[] = ['value' => $website->getId(), 'label' => $website->getName()];
        }


        return $options;
    }

    public function getCondition()
    {
        if ($this->getValue())
        {
            $productIds = $this->_productCollectionFactory->create()->addWebsiteFilter($this->getValue())->getAllIds();
            return ['in' => $productIds];
        }
    }

}