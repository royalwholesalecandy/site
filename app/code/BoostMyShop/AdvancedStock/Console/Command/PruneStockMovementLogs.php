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


class PruneStockMovementLogs extends Command
{
    protected $_model;

    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\StockMovementLogs $model
    )
    {
        $this->_state = $state;
        $this->_model = $model;

        parent::__construct();
    }

    protected function configure()
    {

        $this->setName('bms_advancedstock:prune_stock_movement_log')->setDescription('Prune stock movement logs older than 15 days');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START Prune stock movement logs');

        try{
            $this->_state->setAreaCode('adminhtml');
        }catch(\Exception $ex)
        {
        }

        $this->_model->prune();

        $output->writeln('END Prune stock movement logs');
    }


}
