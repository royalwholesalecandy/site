<?php

namespace BoostMyShop\AdvancedStock\Model;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class StockDiscrepencies
{
    private $objectManagerFactory;
    private $objectManager;
    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        \Magento\Framework\Filesystem $filesystem
    )
    {
        $this->objectManagerFactory = $objectManagerFactory;
        $this->_filesystem = $filesystem;
    }

    public function run($fix = false, $analyser = false)
    {
        $results = [];
        $analyserExist = false;
        if(!$analyser){
            foreach($this->getAnalysers() as $key => $item){
                $item->run($results, $fix);
            }
            $analyserExist = true;
        } else {
            foreach($this->getAnalysers() as $key => $item){
                if($analyser == $key){
                    $item->run($results, $fix);
                    $analyserExist = true;
                }
            }
        }

        if(!$analyserExist)
            throw new \Exception('Unknown analyser');

        $this->hydatesResults($results);

        $this->saveResults($results);

        return $results;
    }

    public function getAnalysers()
    {
        $analysers = [];
        $analysers['missing_warehouse_items'] = $this->getObjectManager()->create('BoostMyShop\AdvancedStock\Model\StockDiscrepencies\MissingWarehouseItems');
        $analysers['missing_stock'] = $this->getObjectManager()->create('BoostMyShop\AdvancedStock\Model\StockDiscrepencies\MissingStock');
        $analysers['unconsistant_stock'] = $this->getObjectManager()->create('BoostMyShop\AdvancedStock\Model\StockDiscrepencies\UnconsistantStock');
        $analysers['missing_stock_items'] = $this->getObjectManager()->create('BoostMyShop\AdvancedStock\Model\StockDiscrepencies\MissingStockItems');
        $analysers['negative_stock'] = $this->getObjectManager()->create('BoostMyShop\AdvancedStock\Model\StockDiscrepencies\NegativeStock');
        $analysers['wrong_stock_item_quantity'] = $this->getObjectManager()->create('BoostMyShop\AdvancedStock\Model\StockDiscrepencies\WrongStockItemQuantity');
        $analysers['wrong_warehouse_item_quantity'] = $this->getObjectManager()->create('BoostMyShop\AdvancedStock\Model\StockDiscrepencies\WrongWarehouseItemQuantity');
        $analysers['wrong_quantity_to_ship'] = $this->getObjectManager()->create('BoostMyShop\AdvancedStock\Model\StockDiscrepencies\WrongQuantityToShip');
        $analysers['unconsistant_reserved_quantity'] = $this->getObjectManager()->create('BoostMyShop\AdvancedStock\Model\StockDiscrepencies\UnconsistantReservedQuantity');
        $analysers['missing_extended_sales_flat_order_items'] = $this->getObjectManager()->create('BoostMyShop\AdvancedStock\Model\StockDiscrepencies\MissingExtendedSalesFlatOrderItems');
        $analysers['wrong_extended_sales_flat_order_items'] = $this->getObjectManager()->create('BoostMyShop\AdvancedStock\Model\StockDiscrepencies\WrongExtendedSalesFlatOrderItems');
        $analysers['stock_item_with_null_quantity'] = $this->getObjectManager()->create('BoostMyShop\AdvancedStock\Model\StockDiscrepencies\StockItemWithNullQuantity');
        $analysers['products_not_sellable'] = $this->getObjectManager()->create('BoostMyShop\AdvancedStock\Model\StockDiscrepencies\ProductsNotSellable');
        $analysers['products_sellable_that_should_not'] = $this->getObjectManager()->create('BoostMyShop\AdvancedStock\Model\StockDiscrepencies\ProductsSellableThatShouldNot');

        return $analysers;
    }

    protected function getObjectManager()
    {
        if (null == $this->objectManager) {
            $area = FrontNameResolver::AREA_CODE;
            $this->objectManager = $this->objectManagerFactory->create($_SERVER);
            $appState = $this->objectManager->get('Magento\Framework\App\State');
            $appState->setAreaCode($area);
            $configLoader = $this->objectManager->get('Magento\Framework\ObjectManager\ConfigLoaderInterface');
            $this->objectManager->configure($configLoader->load($area));
        }
        return $this->objectManager;
    }

    public function hydatesResults(&$results)
    {
        foreach($results as $k => $item)
        {
            if (count($results[$k]['items']) > 0)
                $results[$k]['status'] = 'error';
            else
                $results[$k]['status'] = 'success';
        }
    }

    public function saveResults($results)
    {
        $path = $this->getFilePath();
        file_put_contents($path, json_encode($results));
    }

    public function hasReport()
    {
        return file_exists($this->getFilePath());
    }

    public function getData()
    {
        if ($this->hasReport())
            return json_decode(file_get_contents($this->getFilePath()));
        else
            return '';
    }

    public function getFilePath()
    {
        $dir = $this->_filesystem->getDirectoryWrite(DirectoryList::TMP)->getAbsolutePath();
        if (!file_exists($dir))
            mkdir($dir);
        return $this->_filesystem->getDirectoryWrite(DirectoryList::TMP)->getAbsolutePath('stock_discrepencies.json');
    }
}