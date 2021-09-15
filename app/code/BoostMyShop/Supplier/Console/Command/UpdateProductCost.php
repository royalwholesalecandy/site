<?php

namespace BoostMyShop\Supplier\Console\Command;

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


class UpdateProductCost extends Command
{
    protected $_productHelper;
    protected $_receptionItemFactory;

    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\Supplier\Model\Product $productHelper,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Reception\Item\CollectionFactory $receptionItemFactory
    )
    {
        $this->_productHelper = $productHelper;
        $this->_receptionItemFactory = $receptionItemFactory;
        $this->_state = $state;

        parent::__construct();
    }

    protected function configure()
    {

        $this->setName('bms_supplier:update_product_cost')->setDescription('Calculate product costs based on PO receptions');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START update product cost');

        $this->_state->setAreaCode('adminhtml');

        $collection = $this->getProductIds();
        $count = count($this->getProductIds());
        $i = 1;
        foreach($collection as $productId)
        {
            $value = $this->_productHelper->updateCost($productId);
            $output->writeln($i.' / '.$count.' : product #'.$productId.' - cost is '.$value);
            $i++;
        }

        $output->writeln('END update product cost');
    }


    protected function getProductIds()
    {
        return $this->_receptionItemFactory
            ->create()
            ->getAllProductIds();
    }
}
