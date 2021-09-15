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


class FixReservation extends Command
{
    protected $_pendingOrderCollectionFactory;
    protected $_config;
    protected $_router;
    protected $_state;
    protected $_reservationFixer;

    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory,
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\Warehouse\Item\ReservationFixer $reservationFixer
    )
    {
        $this->_warehouseItemCollectionFactory = $warehouseItemCollectionFactory;
        $this->_state = $state;
        $this->_reservationFixer = $reservationFixer;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('bms_advancedstock:fix_reservation')->setDescription('Fix reservation issues for warehouse items');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START fix reservation');

        try{
            $this->_state->setAreaCode('adminhtml');
        }catch(\Exception $ex)
        {
        }

        $collection = $this->getWarehouseItems();

        $count = $collection->getSize();
        $processed = 0;
        $lastProgessPercent = null;

        if ($collection->getSize() == 0)
            $output->writeln('--> Nothing to Fix !');
        else
            $output->writeln('--> '.$collection->getSize().' reservations to Fix !');

        foreach($collection as $wi)
        {
            $this->_reservationFixer->fixForWarehouseItem($wi);

            $progessPercent = (int)($processed / $count * 100);
            if ($progessPercent != $lastProgessPercent)
            {
                $output->writeln('Progress : '.$progessPercent.'%');
                $lastProgessPercent = $progessPercent;
            }
            $processed++;
        }

        $output->writeln('END fix reservation');
    }

    protected function getWarehouseItems()
    {
        return $this->_warehouseItemCollectionFactory
            ->create()
            ->addUnconsistentFilter()
            ;
    }

}
