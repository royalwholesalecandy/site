<?php
namespace BoostMyShop\AdvancedStock\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\ObjectManagerFactory;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Magento\Framework\Setup\SchemaSetupInterface;


class RemoveAdditionnalStockItem extends Command
{
    protected $_removeAdditionnalStockItem;

    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\RemoveAdditionnalStockItem $removeAdditionnalStockItem
    )
    {
        $this->_state = $state;
        $this->_removeAdditionnalStockItem = $removeAdditionnalStockItem;

        parent::__construct();
    }

    protected function configure()
    {

        $this->setName('bms_advancedstock:remove_additionnal_stock_item')->setDescription('Remove additionnal stock item (before module removal)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START Remove additionnal stock item');

        try{
            $this->_state->setAreaCode('adminhtml');
        }catch(\Exception $ex)
        {
        }

       $this->_removeAdditionnalStockItem->deleteRecords();

        $output->writeln('Remove additionnal stock item');
    }


}
