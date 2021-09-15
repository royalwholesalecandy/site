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


class WarehouseExport extends Command
{

    protected $_warehouseExport;
    protected $_state;
    protected $_warehouseFactory;
    protected $_directoryList;

    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory,
        \BoostMyShop\AdvancedStock\Model\Warehouse\Export $warehouseExport,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    )
    {
        $this->_state = $state;
        $this->_warehouseExport = $warehouseExport;
        $this->_warehouseFactory = $warehouseFactory;
        $this->_directoryList = $directoryList;

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
                'date',
                null,
                InputOption::VALUE_OPTIONAL,
                'Limit date for stock movements'
            )
        ];

        $this->setName('bms_advancedstock:warehouse_export')->setDescription('Export warehouse stock level.')->setDefinition($options);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START warehouse export');

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

        $date = $input->getOption('date');
        if ($date && (strlen($date) != 10))
            throw new \Exception('Please enter date in format: YYYY-MM-DDD');

        $products = $this->_warehouseExport->getProducts($warehouseId, $date);

        $fileName = date('Y-m-d-H:i:s').'_warehouse_export_'.$warehouse->getw_name();
        if ($date)
            $fileName .= '_until_'.$date;
        $fileName .= '.csv';

        $filePath = $this->_directoryList->getPath('var').'/'.$fileName;
        $this->_warehouseExport->convertToCsv($products, $filePath);

        $output->writeln('END warehouse export to : '.$filePath);
    }


}
