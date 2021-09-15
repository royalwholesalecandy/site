<?php
namespace Wanexo\ProductCollection\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


class InstallData implements InstallDataInterface {

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
    public function __construct(EavSetupFactory $eavSetupFactory) {

        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {

        /** 
          @var
          EavSetup
          $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        /**
         *
          Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY, 'is_new', [
            'group'=> 'Wanexo Options',
            'type'=>'int',
            'backend'=>'',
            'frontend'=>'',
            'label'=>'New Product',
            'input'=>'boolean',
            'class'=>'',
            'global'=>\Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
            'visible'=>true,
            'required'=>false,
            'user_defined'=>true,
            'default'=>0,
            'searchable'=>true,
            'filterable'=>true,
            'comparable'=>false,
            'visible_on_front'=>true,
            'used_in_product_listing'=>true,
            'unique'=>false,
            'apply_to'=>'simple,configurable,virtual,bundle,downloadable'
           ]);
		   
		   $eavSetup->addAttribute(
             \Magento\Catalog\Model\Product::ENTITY, 'is_featured', [
            'group'=> 'Wanexo Options',
            'type'=>'int',
            'backend'=>'',
            'frontend'=>'',
            'label'=>'Featured Product',
            'input'=>'boolean',
            'class'=>'',
            'global'=>\Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
            'visible'=>true,
            'required'=>false,
            'user_defined'=>true,
            'default'=>0,
            'searchable'=>true,
            'filterable'=>true,
            'comparable'=>false,
            'visible_on_front'=>true,
            'used_in_product_listing'=>true,
            'unique'=>false,
            'apply_to'=>'simple,configurable,virtual,bundle,downloadable,grouped'
           ]);
    }

}
?>