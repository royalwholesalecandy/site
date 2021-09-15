<?php



namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\Connection\Ajax;



use Magento\Framework\App\Action\Context;

use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Limited;

use Magenest\QuickBooksDesktop\Model\Mapping;

use Magenest\QuickBooksDesktop\Helper\CreateQueue;

use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;

use Magenest\QuickBooksDesktop\Model\QueueFactory;

use Magenest\QuickBooksDesktop\Model\Config\Source\Status;

use Magenest\QuickBooksDesktop\Model\Config\Source\Operation;

use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Priority;



class Sync extends \Magento\Framework\App\Action\Action

{

    /**

     * @var \Magento\Framework\Controller\Result\JsonFactory

     */

    protected $resultJsonFactory;



    /**

     * @var CreateQueue

     */

    protected $_queueHelper;



    /**

     * @var Mapping

     */

    public $_map;



    /**

     * @var QueueFactory

     */

    protected $_queueFactory;



    /**

     * Sync constructor.

     * @param Context $context

     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory

     * @param CreateQueue $createQueue

     * @param QueueFactory $queueFactory

     * @param Mapping $map

     */

    public function __construct(

        Context $context,

        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,

        CreateQueue $createQueue,

        QueueFactory $queueFactory,

        Mapping $map

    )

    {

        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;

        $this->_map = $map;

        $this->_queueHelper = $createQueue;

        $this->_queueFactory = $queueFactory;

    }



    public function execute()

    {

        if ($this->getRequest()->isAjax()) {

            $number = $this->getRequest()->getParam('number');

            $max = $this->getRequest()->getParam('max') - 1;

            $type = $this->getRequest()->getParam('type');
			$result = '';
            if ($type === "customer") {

                $result = $this->syncCustomer($number);

                $limited = Limited::LIMITED_CUSTOMER;

            } elseif ($type === "product") {

                $result = $this->syncProduct($number);

                $limited = Limited::LIMITED_PRODUCT;

            } elseif ($type === "order") {

                $result = $this->syncOrder($number);

                $limited = Limited::LIMITED_ORDER;

            } elseif ($type === "invoice") {

                $result = $this->syncInvoice($number);

                $limited = Limited::LIMITED_INVOICE;

            } elseif ($type === "creditmemo") {

                $result = $this->syncCreditMemo($number);

                $limited = Limited::LIMITED_CREDITMEMO;

            }

            if (!is_numeric($result)) {

                $this->messageManager->addErrorMessage($result);

                return $this->resultJsonFactory->create()->setData([

                    'finish' => 1

                ]);

            } else if ($number == $max) {

                $count = $max * $limited + $result;

                $this->messageManager->addSuccessMessage("Totals $count Queue have been created/updated");

            }

            return $this->resultJsonFactory->create()->setData([

                'finish' => $number == $max ? 1 : 0

            ]);

        }

        return null;

    }



    public function getCollection(array $type, $limited, $number)

    {

        $companyId = $this->_queueHelper->getCompanyId();



        $mappingCollection = $this->_map->getCollection()

            ->addFieldToFilter('company_id', $companyId)

            ->addFieldToFilter(

                'type',

                ["in" => $type]

            )

            ->getColumnValues('entity_id');



        if ($type[0] == Type::QUEUE_CUSTOMER) {

			//$allIds = $this->getTypeCollection($type[0])->getAllIds();

        	$allIds = $this->getCustomerCollectionIds();

		}else if ($type[0] == Type::QUEUE_SALESORDER) {

        	$allIds = $this->getOrderCollectionIds();

		}

		else{

			$allIds = $this->getTypeCollection($type[0])->getAllIds();

		}

		//$allIds = $this->getTypeCollection($type[0])->getAllIds();

       if(!empty($mappingCollection)){
        	$idToQueue = array_diff($allIds, $mappingCollection);
		}else{
			$idToQueue = $allIds;
		}

        $collection = $this->getTypeCollection($type[0])

            ->addFieldToFilter('entity_id', ['in' => $idToQueue])

            ->setPageSize($limited)

            ->setCurPage($number + 1)

            ->setOrder('entity_id', 'ASC');

        return $collection;

    }



    public function getTypeCollection($type)

    {

        if ($type == Type::QUEUE_CUSTOMER) {

            return $this->getCustomerCollection();

        } elseif ($type == Type::QUEUE_PRODUCT) {

            return $this->getProductCollection();

        } elseif ($type == Type::QUEUE_SALESORDER) {

            return $this->getOrderCollection();

        } elseif ($type == Type::QUEUE_INVOICE) {

            return $this->getInvoiceCollection();

        } elseif ($type == Type::QUEUE_CREDITMEMO) {

            return $this->getCreditMemoCollection();

        }

    }



    public function checkQueue($type, $entityId)

    {

        $companyId = $this->_queueHelper->getCompanyId();

        $check = $this->_queueFactory->create()->getCollection()

            ->addFieldToFilter('type', $type)

            ->addFieldToFilter('entity_id', $entityId)

            ->addFieldToFilter('company_id', $companyId)

            ->addFieldToFilter('status', Status::STATUS_QUEUE);

        return $check;

    }



    /**

     * @param $number

     * @return int|string

     */

    protected function syncCustomer($number)

    {

        try {

            $customerCollection = $this->getCollection([Type::QUEUE_CUSTOMER], Limited::LIMITED_CUSTOMER, $number);

            $totals = 0;
			//echo count($customerCollection);die;


            foreach ($customerCollection as $customer) {

                $id = $customer->getId();

                $check = $this->checkQueue('Customer', $id);

                if ($check->count() == 0) {

                    $this->_queueHelper->createCustomerQueue($id, "Add", Operation::OPERATION_ADD);

                }

                $totals++;

            }

            return $totals;

        } catch (\Exception $exception) {

            return $exception->getMessage();

        }

    }



    /**

     * @return mixed

     */

    public function getCustomerCollection()

    {

        return $this->_objectManager->create('\Magento\Customer\Model\ResourceModel\Customer\Collection');

    }

	 public function getCustomerCollectionIds()

    {

			$allIds = array();

			$tableName = 'customer_entity';

			$tableName2 = 'sales_order';

			//$customers = $this->_objectManager->create('Magento\Customer\Model\Customer')->getCollection();

			$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');

			$connection = $resource->getConnection();

			$sql = "Select e.*, sales_order.customer_id FROM " . $tableName." as e left join ".$tableName2." as sales_order on e.entity_id = sales_order.customer_id

			 where sales_order.customer_id != 'NULL' group by e.entity_id";
//die;
			$result = $connection->fetchAll($sql);

			foreach($result as $cust){

				$allIds[] = $cust['entity_id'];

			}

			return $allIds;

    }

    /**

     * @param $number

     * @return int|string

     */

    protected function syncProduct($number)

    {

        try {

            $productCollection = $this->getCollection([Type::QUEUE_PRODUCT], Limited::LIMITED_PRODUCT, $number);

            $totals = 0;



            foreach ($productCollection as $product) {

                /** @var \Magento\Catalog\Model\Product $productModel */

                $productModel = $this->_objectManager->create('\Magento\Catalog\Model\Product');

                $productId = $product->getId();

                $productModel = $productModel->load($productId);

                $qty = $productModel->getExtensionAttributes()->getStockItem()->getQty();

                $type = $productModel->getTypeId();

                $modelCheck = $this->checkQueue('Product', $productId);

                if ($modelCheck->count() == 0) {

                    if ($qty > 0

                        || $type == 'virtual'

                        || $type == 'simple'

                        || $type == 'giftcard'

                        || $type == 'downloadable'

                    ) {

                        $this->_queueHelper->createItemInventoryAddQueue($productId);

                    } else {

                        $this->_queueHelper->createItemNonInventoryAddQueue($productId);

                    }

                }

                $totals++;

            }

            return $totals;

        } catch (\Exception $exception) {

            return $exception->getMessage();

        }

    }



    /**

     * @return mixed

     */

    public function getProductCollection()

    {

        return $this->_objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Collection');

    }



    /**

     * @param $number

     * @return int|string

     */

    protected function syncOrder($number)

    {

        try {

            $companyId = $this->_queueHelper->getCompanyId();

            $orderCollection = $this->getCollection([Type::QUEUE_SALESORDER], Limited::LIMITED_ORDER, $number);



            $totals = 0;

            foreach ($orderCollection as $order) {

                $id = $order->getId();

                $check = $this->checkQueue('SalesOrder', $id);

                if ($check->count() == 0) {

                    if (!$order->getCustomerId()) {

                        $qbId = $this->_map->getCollection()

                            ->addFieldToFilter('company_id', $companyId)

                            ->addFieldToFilter('type', Type::QUEUE_GUEST)

                            ->addFieldToFilter('entity_id', $id)

                            ->getFirstItem()->getData();



                        if (!$qbId) {

                            $this->_queueHelper->createGuestQueue($id, 'Add', Operation::OPERATION_ADD);

                        }

                    }

                    $this->_queueHelper->createTransactionQueue($id, 'SalesOrder', Priority::PRIORITY_SALESORDER);

                }

                $totals++;

            }

            return $totals;

        } catch (\Exception $exception) {

            return $exception->getMessage();

        }

    }



    /**

     * @return mixed

     */

    public function getOrderCollection()

    {

       return $this->_objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Collection');

    }

	public function getOrderCollectionIds()

    {

		$allIds = array();

		$tableName2 = 'customer_entity';

		$tableName = 'sales_order';

		//$customers = $this->_objectManager->create('Magento\Customer\Model\Customer')->getCollection();

		$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');

		$connection = $resource->getConnection();

		$sql2 = 'SELECT i.item_id, i.order_id, i.sku, p.sku from `sales_order_item` as i left join catalog_product_entity as p on p.sku = i.sku left JOIN sales_order as o on o.entity_id = i.order_id left join customer_entity as c on c.entity_id = o.customer_id where p.sku is NULL and c.entity_id is not NULL group by i.order_id ';

		$result2 = $connection->fetchAll($sql2);

		foreach($result2 as $order){

				$allNullProductsOrder[] = $order['order_id'];

		}

		// $sql5 = "Select * FROM magenest_qbd_queue where company_id = 3 and status = 3 and action_name = 'SalesOrderAdd'";

		// $result5 = $connection->fetchAll($sql5);

		// foreach($result5 as $queue){

		// 	$failedQueue[] = $queue['entity_id'];

		// }

		//$quoeueFailed = implode(',',$failedQueue);
        //Select main_table.entity_id, main_table.increment_id, main_table.customer_id, e.entity_id as ecustomer_id FROM sales_order as main_table left join customer_entity as e on e.entity_id = main_table.customer_id where e.entity_id is not NULL and (main_table.status='pending' and main_table.state='new' or main_table.status='processing' and main_table.state='processing')
        $sql = "Select main_table.entity_id, main_table.increment_id, main_table.customer_id, e.entity_id as ecustomer_id FROM " . $tableName." as main_table left join ".$tableName2." as e on e.entity_id = main_table.customer_id where e.entity_id is not NULL and (main_table.status='pending' and main_table.state='new' or main_table.status='processing' and main_table.state='processing')";
        
        // echo $sql;

        // die;
        
		$result = $connection->fetchAll($sql);

		foreach($result as $cust){

			$allIds[] = $cust['entity_id'];

        }
        

        $idToQueue = array_diff($allIds, $allNullProductsOrder);
        
		return $idToQueue;

    }

    /**

     * @param $number

     * @return int|string

     */

    protected function syncInvoice($number)

    {

        try {

            $invoiceCollection = $this->getCollection([Type::QUEUE_INVOICE, Type::QUEUE_RECEIVEPAYMENT], Limited::LIMITED_INVOICE, $number);

            $totals = 0;



            foreach ($invoiceCollection as $invoice) {

                $id = $invoice->getId();



                $check = $this->checkQueue('Invoice', $id);

                if ($check->count() == 0) {

                    $this->_queueHelper->createTransactionQueue($id, 'Invoice', Priority::PRIORITY_INVOICE);



                }

                $totals++;

                if ($invoice->getState() == 2) { // Paid Invoice

                    $check = $this->checkQueue('ReceivePayment', $id);

                    if ($check->count() == 0) {

                        $this->_queueHelper->createTransactionQueue($id, 'ReceivePayment', Priority::PRIORITY_RECEIVEPAYMENT);

                    }

                    $totals++;

                }

            }

            return $totals;

        } catch (\Exception $exception) {

            return $exception->getMessage();

        }

    }



    /**

     * @return mixed

     */

    public function getInvoiceCollection()

    {

		$allIds = array();

		$tableName2 = 'customer_entity';

		$tableName = 'sales_order';

		//$customers = $this->_objectManager->create('Magento\Customer\Model\Customer')->getCollection();

		$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');

		$connection = $resource->getConnection();

		$sql2 = 'SELECT i.item_id, i.order_id, i.sku, p.sku from `sales_order_item` as i left join catalog_product_entity as p on p.sku = i.sku left JOIN sales_order as o on o.entity_id = i.order_id left join customer_entity as c on c.entity_id = o.customer_id where p.sku is NULL and c.entity_id is not NULL group by i.order_id ';

		$result2 = $connection->fetchAll($sql2);

		foreach($result2 as $order){

				$allNullProductsOrder[] = $order['order_id'];

		}

		$sql = "Select main_table.entity_id, main_table.increment_id, main_table.customer_id, e.entity_id as ecustomer_id FROM " . $tableName." as main_table left join ".$tableName2." as e on e.entity_id = main_table.customer_id where e.entity_id is not NULL";

		$result = $connection->fetchAll($sql);

		foreach($result as $cust){

			$allIds[] = $cust['entity_id'];

		}



		$orderIds = array_diff($allIds, $allNullProductsOrder);

        return $this->_objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Invoice\Collection')->addFieldToFilter('order_id', array('IN' => $orderIds));

    }



    /**

     * @param $number

     * @return int|string

     */

    protected function syncCreditMemo($number)

    {

        try {

            $memoCollection = $this->getCollection([Type::QUEUE_CREDITMEMO], Limited::LIMITED_CREDITMEMO, $number);

            $totals = 0;

            foreach ($memoCollection as $memo) {

                $id = $memo->getId();

                $check = $this->checkQueue('CreditMemo', $id);

                if ($check->count() == 0) {

                    $this->_queueHelper->createTransactionQueue($id, 'CreditMemo', Priority::PRIORITY_CREDITMEMO);

                }

                $totals++;

            }

            return $totals;

        } catch (\Exception $exception) {

            return $exception->getMessage();

        }

    }



    /**

     * @return mixed

     */

    public function getCreditMemoCollection()

    {

		$allIds = array();

		$tableName2 = 'customer_entity';

		$tableName = 'sales_order';

		//$customers = $this->_objectManager->create('Magento\Customer\Model\Customer')->getCollection();

		$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');

		$connection = $resource->getConnection();

		$sql2 = 'SELECT i.item_id, i.order_id, i.sku, p.sku from `sales_order_item` as i left join catalog_product_entity as p on p.sku = i.sku left JOIN sales_order as o on o.entity_id = i.order_id left join customer_entity as c on c.entity_id = o.customer_id where p.sku is NULL and c.entity_id is not NULL group by i.order_id ';

		$result2 = $connection->fetchAll($sql2);

		foreach($result2 as $order){

				$allNullProductsOrder[] = $order['order_id'];

		}

		$sql = "Select main_table.entity_id, main_table.increment_id, main_table.customer_id, e.entity_id as ecustomer_id FROM " . $tableName." as main_table left join ".$tableName2." as e on e.entity_id = main_table.customer_id where e.entity_id is not NULL";

		$result = $connection->fetchAll($sql);

		foreach($result as $cust){

			$allIds[] = $cust['entity_id'];

		}



		$orderIds = array_diff($allIds, $allNullProductsOrder);

        return $this->_objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection')->addFieldToFilter('order_id', array('IN' => $orderIds));

    }



}

