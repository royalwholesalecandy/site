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


class CheckStockDiscrepencies extends Command
{
    protected $_stockDiscrepencies;

    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\StockDiscrepencies $stockDiscrepencies
    )
    {
        $this->_stockDiscrepencies = $stockDiscrepencies;
        $this->_state = $state;

        parent::__construct();
    }

    protected function configure()
    {
        $options = [
            new InputOption(
                'fix',
                null,
                null,
                'Fix discrepencies'
            ),
            new InputOption(
                'analyser',
                null,
                InputOption::VALUE_OPTIONAL,
                'Analyser'
            )
        ];

        $this->setName('bms_advancedstock:check_stock_discrepencies')->setDescription('Check every discrepencies for stock')->setDefinition($options);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START check stock discrepencies');

        try{
            $this->_state->setAreaCode('adminhtml');
        }catch(\Exception $ex)
        {
        }

        $fix      = $input->getOption('fix');
        $analyser = $input->getOption('analyser');
        
        $this->_stockDiscrepencies->run($fix, $analyser);

        //run a second time to updatre discrepencies list
        if ($fix)
            $this->_stockDiscrepencies->run(false,$analyser);

        $output->writeln('END check stock discrepencies : you can consult the results in store > configuration > advanced stock > stock discrepency report');
    }


}
