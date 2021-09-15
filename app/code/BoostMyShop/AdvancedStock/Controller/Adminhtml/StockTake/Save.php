<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake;

/**
 * Class Save
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Save extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake
{

    /**
     * @var \BoostMyShop\AdvancedStock\Model\StockTake
     */
    protected $_stockTake;

    /**
     * @return $this
     */
    public function execute()
    {

        try {

            $clean = $this->_extractData();

            if (isset($clean['sta_id']) && !empty($clean['sta_id'])) {

                $this->_updateRecord($clean);

            } else {

                $this->_insertNewRecord($clean);

            }

            $this->messageManager->addSuccess(__('Stock Take successfully saved'));
            return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/edit', ['_current' => true, 'id' => $this->_stockTake->getId()]);

        }catch(\Exception $e){

            $this->messageManager->addErrorMessage(__('An error occurred : '.$e->getMessage()));
            return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/index');

        }

    }

    /**
     * @return array $clean
     */
    protected function _extractData()
    {

        $data = $this->getRequest()->getPost('stocktake');

        $clean = filter_var_array(
            $data,
            [
                'sta_id' => FILTER_VALIDATE_INT,
                'sta_name' => FILTER_SANITIZE_STRING,
                'sta_website' => FILTER_SANITIZE_STRING,
                'sta_product_selection' => FILTER_SANITIZE_STRING,
                'sta_warehouse_id' => FILTER_VALIDATE_INT,
                'sta_status' => FILTER_SANITIZE_STRING,
                'sta_per_location' => FILTER_VALIDATE_INT,
                'sta_notes' => FILTER_SANITIZE_STRING
            ]
        );

        if(isset($data['sta_manufacturers']) && is_array($data['sta_manufacturers'])){

            foreach($data['sta_manufacturers'] as $manufacturerId){

                if($manufacturerId = filter_var($manufacturerId, FILTER_VALIDATE_INT)){

                    $clean['sta_manufacturers'][] = $manufacturerId;

                }

            }

        }

        if (isset($data['scanned_quantities']) && is_array($data['scanned_quantities'])) {

            foreach ($data['scanned_quantities'] as $stockTakeItemId => $scannedQty) {

                if ($stockTakeItemId = filter_var($stockTakeItemId, FILTER_VALIDATE_INT)
                ) {

                    $clean['scanned_quantities'][$stockTakeItemId] = filter_var($scannedQty, FILTER_VALIDATE_INT);

                }

            }

        }

        if (isset($data['add']) && is_array($data['add'])) {

            foreach (array_keys($data['add']) as $values) {

                $values = json_decode(base64_decode($values), true);

                if (!empty($values) && is_array($values)) {

                    $sku = (isset($values['sku'])) ? filter_var($values['sku'], FILTER_SANITIZE_STRING) : null;
                    $name = (isset($values['name'])) ? filter_var($values['name'], FILTER_SANITIZE_STRING) : null;
                    $location = (isset($values['location'])) ? filter_var($values['location'], FILTER_SANITIZE_STRING) : null;
                    $qty = (isset($values['qty'])) ? filter_var($values['qty'], FILTER_VALIDATE_INT) : null;
                    $manufacturer = (isset($values['manufacturer'])) ? filter_var($values['manufacturer'], FILTER_SANITIZE_STRING) : null;

                    if (!empty($sku) && !empty($name)) {
                        $clean['add'][] = [
                            'sku' => $sku,
                            'name' => $name,
                            'location' => $location,
                            'qty' => $qty,
                            'manufacturer' => $manufacturer
                        ];
                    }

                }

            }

        }

        return $clean;

    }

    /**
     * @param array $clean
     */
    protected function _updateRecord($clean)
    {

        $this->_stockTake = $this->_stockTakeFactory->create()->load($clean['sta_id'])
            ->setData('sta_name', $clean['sta_name'])
            ->setData('sta_product_selection', $clean['sta_product_selection'])
            ->setData('sta_warehouse_id', $clean['sta_warehouse_id'])
            ->setData('sta_status', $clean['sta_status'])
            ->setData('sta_website', $clean['sta_website'])
            ->setData('sta_per_location', $clean['sta_per_location'])
            ->setData('sta_notes', $clean['sta_notes'])
            ->save();

        //Add products
        if (isset($clean['add'])) {
            $this->_stockTake->addItems($clean['add']);
        }

        if (isset($clean['scanned_quantities'])) {
           $this->_stockTake->updateItemsScannedQty($clean['scanned_quantities']);
        }

        $this->checkImport($this->_stockTake);

    }

    /**
     * @param array $clean
     */
    protected function _insertNewRecord($clean)
    {

        $this->_stockTake = $this->_stockTakeFactory->create()
            ->setData('sta_name', $clean['sta_name'])
            ->setData('sta_product_selection', $clean['sta_product_selection'])
            ->setData('sta_warehouse_id', $clean['sta_warehouse_id'])
            ->setData('sta_status', $clean['sta_status'])
            ->setData('sta_website', $clean['sta_website'])
            ->setData('sta_per_location', $clean['sta_per_location'])
            ->setData('sta_notes', $clean['sta_notes'])
            ->setData('sta_manager_id', $this->_backendAuthSession->getUser()->getId());

        if($clean['sta_product_selection'] == \BoostMyShop\AdvancedStock\Model\StockTake::PRODUCT_SELECTION_MANUFACTURER){

            $this->_stockTake->setData('sta_manufacturers', implode(',',$clean['sta_manufacturers']));

        }

        $this->_stockTake->save();

    }

    /**
     * Import CSV file with scanned quantities
     *
     * @param $stockTake
     */
    protected function checkImport($stockTake)
    {
        try
        {
            $adapter = $this->_httpFactory->create();
            if ($adapter->isValid('import_file')) {
                $destinationFolder = $this->_dir->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
                $uploader = $this->_uploaderFactory->create(array('fileId' => 'import_file'));
                $uploader->setAllowRenameFiles(true);
                $uploader->setAllowedExtensions(['csv', 'txt']);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destinationFolder);
                $fullPath = $result['path'].$result['file'];

                $importHandler = $this->_objectManager->create('BoostMyShop\AdvancedStock\Model\StockTake\ImportHandler');
                $result = $importHandler->importFromCsvFile($stockTake, $fullPath);
                $this->messageManager->addSuccess(__('Csv file has been imported : %1 row(s) imported', $result['success']));

                foreach($result['errors'] as $error)
                    $this->messageManager->addError($error);

            }

        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__('An error occured during import : %1', $ex->getMessage()));
        }
    }

}