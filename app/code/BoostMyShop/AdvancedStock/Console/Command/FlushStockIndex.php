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


class FlushStockIndex extends Command
{
    protected $_helper;

    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\FlushStockIndex $helper
    )
    {
        $this->_state = $state;
        $this->_helper = $helper;

        parent::__construct();
    }

    protected function configure()
    {

        $this->setName('bms_advancedstock:flush_stock_index')->setDescription('Flush stock index (to reindex from scratch after)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START flush stock index');

        try{
            $this->_state->setAreaCode('adminhtml');
        }catch(\Exception $ex)
        {
        }

        $this->_helper->flush();

        $output->writeln('Remove flush stock index');
    }


}
