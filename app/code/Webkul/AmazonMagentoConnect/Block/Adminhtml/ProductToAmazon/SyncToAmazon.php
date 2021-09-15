<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Block\Adminhtml\ProductToAmazon;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class SyncToAmazon extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param CollectionFactory                     $productCollectionFactory
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * For get selected mage product count.
     * @return int
     */
    public function getProductsListForAmazon()
    {
        $params = $this->getRequest()->getParams();

        $mageProCollection = $this->productCollectionFactory
                            ->create()
                            ->addFieldToFilter(
                                'entity_id',
                                ['in'=>$params['mageProEntityIds']]
                            )->getData();
        return $mageProCollection;
    }
}
