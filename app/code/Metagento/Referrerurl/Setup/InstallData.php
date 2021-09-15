<?php
namespace Metagento\Referrerurl\Setup;

use Magento\Framework\Setup\ModuleContextInterface;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallSchema
 * @package Metagento\Faq\Setup
 */
class InstallData implements
    \Magento\Framework\Setup\InstallDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
    }


    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerSetup->removeAttribute(Customer::ENTITY,'referrer_url');
        $customerSetup->addAttribute(Customer::ENTITY, 'referrer_url', [
            'type'         => 'varchar',
            'label'        => 'Referrer URL',
            'input'        => 'text',
            'required'     => false,
            'visible'      => true,
            'user_defined' => true,
            'sort_order'   => 1000,
            'position'     => 1000,
            'system'       => 0,
        ]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        /** @var $attributeSet AttributeSet */
        $attributeSet     = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);


        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'referrer_url')
                                   ->addData([
                                                 'attribute_set_id'      => $attributeSetId,
                                                 'attribute_group_id'    => $attributeGroupId,
                                                 'used_in_forms'         => ['adminhtml_customer'],
                                                 'is_used_in_grid'       => 1,
                                                 'is_visible_in_grid'    => 1,
                                                 'is_filterable_in_grid' => 1,
                                                 'is_searchable_in_grid' => 1,
                                             ]);
        $attribute->save();
    }
}
