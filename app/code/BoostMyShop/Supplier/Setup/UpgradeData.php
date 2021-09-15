<?php

namespace BoostMyShop\Supplier\Setup;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;


class UpgradeData implements UpgradeDataInterface
{
    protected $eavSetupFactory;
    protected $_productHelper;
    protected $_transitCollectionFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \BoostMyShop\Supplier\Model\Product $productHelper,
        \BoostMyShop\Supplier\Model\ResourceModel\Transit\CollectionFactory $transitCollectionFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->_productHelper = $productHelper;
        $this->_transitCollectionFactory = $transitCollectionFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '0.0.25') < 0)
        {
            //change qty to receive attribute type
            $eavSetup->removeAttribute('catalog_product', 'qty_to_receive');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'qty_to_receive',
                [
                    'type' => 'int',
                    'visible' => false,
                    'required' => false,
                    'default' => 0,
                ]
            );

            //populate qty to receive again
            $productIds = $this->_transitCollectionFactory->create()->init(false)->getAllIds();
            foreach($productIds as $productId)
            {
                $this->_productHelper->updateQuantityToReceive($productId);
            }

        }

        $setup->endSetup();
    }

}
