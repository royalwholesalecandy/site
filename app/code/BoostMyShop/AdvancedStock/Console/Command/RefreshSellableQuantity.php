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


class RefreshSellableQuantity extends Command
{
    protected $_config;
    protected $_router;
    protected $_state;
    protected $_websiteCollectionFactory;
    protected $_productCollectionFactory;
    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Product\PendingOrders\CollectionFactory $pendingOrderCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Router $router,
        \Magento\Framework\App\State $state
    )
    {
        $this->_pendingOrderCollectionFactory = $pendingOrderCollectionFactory;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_config = $config;
        $this->_router = $router;
        $this->_state = $state;


        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('bms_advancedstock:refresh_sellable_quantity')->setDescription('Refresh sellable quantity catalog inventory stock item');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START quantity sellable');

        try{
            $this->_state->setAreaCode('adminhtml');
        }catch(\Exception $ex)
        {
        }

        $productIds = $this->getProductIds();
        $websiteIds = $this->_websiteCollectionFactory->create()->getAllIds();
        $websiteIds[] = 0;

        $count = count($productIds) * count($websiteIds);
        $processed = 0;
        $lastProgessPercent = null;

        try
        {
            foreach($productIds as $productId)
            {
                foreach($websiteIds as $websiteId) {
                    $this->_router->updateSalableQuantity($websiteId, $productId);
                    $progessPercent = (int)($processed / $count * 100);
                    if ($progessPercent != $lastProgessPercent) {
                        $output->writeln('Progress : ' . $progessPercent . '%');
                        $lastProgessPercent = $progessPercent;
                    }
                    $processed++;
                }
            }
        }
        catch(\Exception $ex)
        {
            $output->writeln('##################################################');
            $output->writeln('ERROR : '.$ex->getMessage());
            if ($ex->getPrevious())
                $output->writeln('PREVIOUS ERROR : '.$ex->getPrevious()->getMessage());
            $output->writeln('Trace : '.$ex->getTraceAsString());
            if ($ex->getPrevious())
                $output->writeln('PREVIOUS Trace : '.$ex->getPrevious()->getTraceAsString());
            $output->writeln('##################################################');
        }


        $output->writeln('END quantity sellable');
    }

    protected function getProductIds()
    {
        return $this->_productCollectionFactory->create()->getAllIds();
    }

}
