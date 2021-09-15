<?php
namespace Codealist\CustomerAttributes\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Customer\Model\Customer;
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;
    /**
     * InstallData constructor.
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }
    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        
        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $attributesToAdd = [];



     




        /********* BEGIN: Add TEXT attribute ********* */
        $attrCode = 'custom_net_terms';
        $customerSetup->addAttribute(Customer::ENTITY, $attrCode, [
            'type' => 'varchar',
            'label' => 'Net Terms',
            'input' => 'text',
            'position' => 101,
            'required' => false,
            'default' => "",
            'system' => false
        ]);
        $attributesToAdd[] = $attrCode;
		$attrCode = 'custom_po_limit';
        $customerSetup->addAttribute(Customer::ENTITY, $attrCode, [
            'type' => 'varchar',
            'label' => 'PO Limit',
            'input' => 'text',
            'position' => 102,
            'required' => false,
            'default' => "",
            'system' => false
        ]);
        $attributesToAdd[] = $attrCode;
		$attrCode = 'custom_po_credit';
        $customerSetup->addAttribute(Customer::ENTITY, $attrCode, [
            'type' => 'varchar',
            'label' => 'PO Credit',
            'input' => 'text',
            'position' => 103,
            'required' => false,
            'default' => "",
            'system' => false
        ]);
        $attributesToAdd[] = $attrCode;
        /********* END ********* */




       




        /** Add the attributes to the attribute set and the common forms */
        foreach ($attributesToAdd as $code) {
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $code);
            $attribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer']
            ]);
            $attribute->save();
        }


        $setup->startSetup();
    }

}