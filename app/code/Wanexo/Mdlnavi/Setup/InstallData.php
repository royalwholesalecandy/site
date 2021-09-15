<?php
namespace Wanexo\Mdlnavi\Setup;
 
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
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
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
 
        /**
         * Add attributes to the eav/attribute
         */
		$eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'wan_navtype',
			[
			'type' => 'varchar',
            'label' => 'Menu Type',
            'input' => 'select',
            'source' => 'Wanexo\Mdlnavi\Model\Source\Bannertype',
            'required' => false,
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
            'group' => 'Wanexo Navigation',
			'position' => 100,
			]
        );
		
		$eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'wan_labelcolor',
			[
			'type' => 'varchar',
            'label' => 'Label Color',
            'input' => 'select',
            'source' => 'Wanexo\Mdlnavi\Model\Source\Labelcolor',
            'required' => false,
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
            'group' => 'Wanexo Navigation',
			'position' => 200,
			]
        );
 
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'wan_label',
			[
			'group' => 'Wanexo Navigation',
			'type' => 'varchar',
			'label' => 'Label',
			'input' => 'text',
			'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
			'visible' => true,
			'required' => false,
			'user_defined' => true,
			'default' => '',
			'position' => 300,
			]
        );
		
		$eavSetup->addAttribute(
			\Magento\Catalog\Model\Category::ENTITY,
			'wan_topblock',
			[
				'group' => 'Wanexo Navigation',
				'input' => 'textarea',
				'type' => 'text',
				'label' => 'Top Block',
				'note' => "For top-level categories only",
				'backend' => '',
				'visible' => true,
				'required' => false,
				'visible_on_front' => true,
				'wysiwyg_enabled' => true,
				'is_html_allowed_on_front'	=> true,
				'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
				'position' => 400,
			]
		);
		
		$eavSetup->addAttribute(
			\Magento\Catalog\Model\Category::ENTITY,
			'wan_bottomblock',
			[
				'group' => 'Wanexo Navigation',
				'input' => 'textarea',
				'type' => 'text',
				'label' => 'Bottom Block',
				'note' => "For top-level categories only",
				'backend' => '',
				'visible' => true,
				'required' => false,
				'visible_on_front' => true,
				'wysiwyg_enabled' => true,
				'is_html_allowed_on_front'	=> true,
				'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
				'position' => 500,
			]
		);
		
		$eavSetup->addAttribute(
			\Magento\Catalog\Model\Category::ENTITY,
			'wan_rightblock',
			[
				'group' => 'Wanexo Navigation',
				'input' => 'textarea',
				'type' => 'text',
				'label' => 'Right Block',
				'note' => "For top-level categories only",
				'backend' => '',
				'visible' => true,
				'required' => false,
				'visible_on_front' => true,
				'wysiwyg_enabled' => true,
				'is_html_allowed_on_front'	=> true,
				'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
				'position' => 600,
			]
		);
		
		$eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'wan_rblockwidth',
			[
			'type' => 'varchar',
            'label' => 'Right Block Width',
            'input' => 'select',
            'source' => 'Wanexo\Mdlnavi\Model\Source\Rblockwidth',
            'required' => false,
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
            'group' => 'Wanexo Navigation',
			'position' => 700,
			]
        );
		
		$eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'wan_subcat',
			[
			'type' => 'varchar',
            'label' => 'Enable Subcategories',
            'input' => 'select',
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'required' => false,
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
            'group' => 'Wanexo Navigation',
			'position' => 800,
			]
        );
		
		$eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'wan_nocol',
			[
			'type' => 'varchar',
            'label' => 'Select number of column',
            'input' => 'select',
            'source' => 'Wanexo\Mdlnavi\Model\Source\Nocol',
            'required' => false,
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
            'group' => 'Wanexo Navigation',
			'position' => 900,
			]
        );
    }
}