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


class UpdateQuantityToReceive extends Command
{
    protected $_productHelper;
    protected $_productCollectionFactory;

    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\Supplier\Model\Product $productHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    )
    {
        $this->_productHelper = $productHelper;
        $this->_state = $state;
        $this->_productCollectionFactory = $productCollectionFactory;

        parent::__construct();
    }

    protected function configure()
    {

        $this->setName('bms_supplier:update_qty_to_receive')->setDescription('Update quantity to receive for all products');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START update qty to receive');

        $this->_state->setAreaCode('adminhtml');

        $collection = $this->getProductIds();
        $count = count($this->getProductIds());
        $i = 1;
        foreach($collection as $productId)
        {
            $this->_productHelper->updateQuantityToReceive($productId);
            $output->writeln($i.' / '.$count.' : product #'.$productId);
            $i++;
        }

        $output->writeln('END update qty to receive');
    }


    protected function getProductIds()
    {
        return $this->_productCollectionFactory
            ->create()
            ->getAllIds();
    }
}
