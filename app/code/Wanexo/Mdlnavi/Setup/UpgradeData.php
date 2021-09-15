<?php
namespace Wanexo\Mdlnavi\Setup;
 
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
 
    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
 
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
		
		 if (version_compare($context->getVersion(), '2.0.3') < 0) {
        /**
         * Add attributes to the eav/attribute
         */
		$eavSetup->addAttribute(
		\Magento\Catalog\Model\Category::ENTITY, 
		"wan_thumbnail",
		[
			"type"     => "varchar",
			"frontend" => "",
			"backend"  => "Magento\Catalog\Model\Category\Attribute\Backend\Image",
			"label"    => "Menu Thumbnail",
			"input"    => "image",
			"class"    => "custom-category-image",
			"source"   => "",
			"global" => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
			"visible"  => true,
			"required" => false,
			"user_defined"  => false,
			"default" => "",
			 'group' => 'Wanexo Navigation',
			'note' => "For top-level categories only",
			'position' => 850,
		]
	);
		 }
		  if (version_compare($context->getVersion(), '2.0.4') < 0) {
        /**
         * Add attributes to the eav/attribute
         */
		$eavSetup->addAttribute(
		\Magento\Catalog\Model\Category::ENTITY, 
		"wan_icon",
		[
			"type"     => "varchar",
			"frontend" => "",
			"backend"  => "Magento\Catalog\Model\Category\Attribute\Backend\Image",
			"label"    => "Menu Icon",
			"input"    => "image",
			"class"    => "custom-category-icon",
			"source"   => "",
			"global" => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
			"visible"  => true,
			"required" => false,
			"user_defined"  => false,
			"default" => "",
			 'group' => 'Wanexo Navigation',
			'note' => "For top-level categories only",
			'position' => 870,
		]
	);
		 }
		 
		if (version_compare($context->getVersion(), '2.0.5') < 0) {
		
        $eavSetup->addAttribute(
        \Magento\Catalog\Model\Category::ENTITY,
		'bg_position',
		[
            'type' => 'varchar',
            'label' => 'BG Position',
            'input' => 'select',
            'source' => 'Wanexo\Mdlnavi\Model\Source\Bgposition',
            'required' => false,
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
            'group' => 'Wanexo Navigation',
			'position' => 250,
        ]
    );
		}
		
		if (version_compare($context->getVersion(), '2.0.6') < 0) {
		
       $eavSetup->addAttribute(
		\Magento\Catalog\Model\Category::ENTITY, 
		"bgimage",
		[
			"type"     => "varchar",
			"frontend" => "",
			"backend"  => "Magento\Catalog\Model\Category\Attribute\Backend\Image",
			"label"    => "Megamenu Background Image",
			"input"    => "image",
			"class"    => "custom-category-bg",
			"source"   => "",
			"global" => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
			"visible"  => true,
			"required" => false,
			"user_defined"  => false,
			"default" => "",
			 'group' => 'Wanexo Navigation',
			'note' => "For top-level categories only",
			'position' => 200,
		]
	);
		}
		
		if (version_compare($context->getVersion(), '2.0.7') < 0) {
		
			$eavSetup->addAttribute(
			 \Magento\Catalog\Model\Category::ENTITY,
			 'custom_link',
			 [
				 'type' => 'varchar',
				 'label' => 'Page/Custom Links',
				 'input' => 'select',
				 'source' => 'Wanexo\Mdlnavi\Model\Source\CustomLink',
				 'required' => false,
				 'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
				 'group' => 'Wanexo Navigation',
				 'position' => 250,
			 ]
		 );
		}
		
		if (version_compare($context->getVersion(), '2.0.8') < 0) {
		
			$eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
				'wan_clink',
				[
					'group' => 'Wanexo Navigation',
					'type' => 'varchar',
					'label' => 'Custom Links',
					'input' => 'text',
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'visible' => true,
					'required' => false,
					'user_defined' => true,
					'default' => '',
					'position' => 300,
				]
			);
		}
		
		if (version_compare($context->getVersion(), '2.0.9') < 0) {
			
			$eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
				'wan_page',
				[
					'group' => 'Wanexo Navigation',
					'type' => 'varchar',
					'label' => 'Page Link',
					'input' => 'select',
					'source' => 'Wanexo\Mdlnavi\Model\Source\Page',
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'visible' => true,
					'required' => false,
					'position' => 350,
				]
			);
		}
		
		if (version_compare($context->getVersion(), '2.1.1') < 0) {
			
			$eavSetup->addAttribute(
				\Magento\Catalog\Model\Category::ENTITY,
				'wan_mblockwidth',
				[
					'type' => 'varchar',
					'label' => 'Main Block Width',
					'input' => 'select',
					'source' => 'Wanexo\Mdlnavi\Model\Source\Mblockwidth',
					'required' => false,
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'group' => 'Wanexo Navigation',
					'position' => 700,
				]
            );
		}
		
		
    }
}