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


class WarehouseImport extends Command
{

    protected $_warehouseImport;
    protected $_warehouseFactory;
    protected $_state;

    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\Warehouse\Import $warehouseImport,
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory

    )
    {
        $this->_state = $state;
        $this->_warehouseImport = $warehouseImport;
        $this->_warehouseFactory = $warehouseFactory;

        parent::__construct();
    }

    protected function configure()
    {
        $options = [
            new InputOption(
                'warehouse_id',
                null,
                InputOption::VALUE_REQUIRED,
                'Warehouse ID (required)'
            ),
            new InputOption(
                'file_path',
                null,
                InputOption::VALUE_REQUIRED,
                'Csv path'
            )
        ];

        $this->setName('bms_advancedstock:warehouse_import')->setDescription('Import warehouse stock level.')->setDefinition($options);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START warehouse import');

        try{
            $this->_state->setAreaCode('adminhtml');
        }catch(\Exception $ex)
        {
        }

        $warehouseId = $input->getOption('warehouse_id');
        if (!$warehouseId)
            throw new \Exception('Warehouse id missing');
        $warehouse = $this->_warehouseFactory->create()->load($warehouseId);
        if (!$warehouse->getId())
            throw new \Exception('Warehouse #'.$warehouseId.' does not exist');

        $filePath = $input->getOption('file_path');
        if (!$filePath)
            throw new \Exception('File path is missing');

        $output->writeln($this->_warehouseImport->process($warehouseId, $filePath).' records updated');

        //Store results
        file_put_contents($filePath.'.import.log', json_encode($this->_warehouseImport->getResult(), JSON_PRETTY_PRINT));
        $output->writeln('Report available in file '.$filePath.'.import.log');

        $output->writeln('END warehouse import');
    }


}
