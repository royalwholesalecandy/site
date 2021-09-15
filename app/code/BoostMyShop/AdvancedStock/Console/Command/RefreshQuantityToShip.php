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


class RefreshQuantityToShip extends Command
{
    protected $_pendingOrderCollectionFactory;
    protected $_config;
    protected $_router;
    protected $_state;
    protected $_warehouseCollectionFactory;

    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Product\PendingOrders\CollectionFactory $pendingOrderCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Router $router,
        \Magento\Framework\App\State $state
    )
    {
        $this->_pendingOrderCollectionFactory = $pendingOrderCollectionFactory;
        $this->_config = $config;
        $this->_router = $router;
        $this->_state = $state;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('bms_advancedstock:refresh_quantity_to_ship')->setDescription('Refresh quantity to ship for warehouse items');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START quantity to ship');

        try{
            $this->_state->setAreaCode('adminhtml');
        }catch(\Exception $ex)
        {
        }

        $productIds = $this->getProductIds();

        $count = count($productIds);
        $processed = 0;
        $lastProgessPercent = null;

        $warehouseIds = $this->_warehouseCollectionFactory->create()->getAllIds();

        if (is_array($productIds))
        {
            foreach($productIds as $productId)
            {
                if (!$productId)
					continue;

                foreach($warehouseIds as $warehouseId)
                    $this->_router->updateQuantityToShip($productId, $warehouseId);

                $progessPercent = (int)($processed / $count * 100);
                if ($progessPercent != $lastProgessPercent)
                {
                    $output->writeln('Progress : '.$progessPercent.'%');
                    $lastProgessPercent = $progessPercent;
                }
                $processed++;
            }
        }

        $output->writeln('END quantity to ship');
    }

    protected function getProductIds()
    {
        return $this->_pendingOrderCollectionFactory
                                ->create()
                                ->addExtendedDetails()
                                ->addOrderDetails()
                                ->addStatusesFilter($this->_config->getPendingOrderStatuses())
                                ->getAllProductIds();
    }

}
