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



class Check extends \Magento\Framework\App\Action\Action

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

            $type = $this->getRequest()->getParam('type');

            if ($type === "customer") {

                $result = $this->getCountCustomer();

            } elseif ($type === "product") {

                $result = $this->getCountProduct();

            } elseif ($type === "order") {

                $result = $this->getCountOrder();

            } elseif ($type === "invoice") {

                $result = $this->getCountInvoice();

            } elseif ($type === "creditmemo") {

                $result = $this->getCountCreditMemo();

            }

           if($result == 0){

               $this->messageManager->addSuccessMessage('Totals 0 Queue have been created/updated');

           }

            return $this->resultJsonFactory->create()->setData([

                'count' => $result

            ]);

        }

        return null;

    }



    public function getCollection(array $type)

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

        	$allIds = $this->getCustomerCollectionIds();

		}else if ($type[0] == Type::QUEUE_SALESORDER) {

        	$allIds = $this->getOrderCollectionIds();

		}

		else{

			$allIds = $this->getTypeCollection($type[0])->getAllIds();

		}

		//$allIds = $this->getTypeCollection($type[0])->getAllIds();

		//echo $mappingCollection->getSelect();

		//echo count($allIds).'---'.count($mappingCollection);
		if(!empty($mappingCollection)){
        	$idToQueue = array_diff($allIds, $mappingCollection);
		}else{
			$idToQueue = $allIds;
		}
		//echo count($idToQueue);
		//print_r($idToQueue);die;
        $collection = $this->getTypeCollection($type[0])

            ->addFieldToFilter('entity_id', ['in' => $idToQueue]);
		//echo count($collection);die;
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



    /**

     * @return float

     */

    public function getCountCustomer()

    {

        $customerCollection = $this->getCollection([Type::QUEUE_CUSTOMER]);

		//echo $customerCollection->count();die;

        return (ceil(($customerCollection->count()) / Limited::LIMITED_CUSTOMER));

    }



    /**

     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection|mixed

     */

    public function getCustomerCollection()

    {

        return $this->_objectManager->create('\Magento\Customer\Model\ResourceModel\Customer\Collection');

    }

	/**

     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection|mixed

     */

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

			$result = $connection->fetchAll($sql);

			foreach($result as $cust){

				$allIds[] = $cust['entity_id'];

			}

			return $allIds;

    }

	

    /**

     * @return float

     */

    public function getCountProduct()

    {



        $productCollection = $this->getCollection([Type::QUEUE_PRODUCT]);

        return (ceil(($productCollection->count()) / Limited::LIMITED_PRODUCT));

    }



    /**

     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection|mixed

     */

    public function getProductCollection()

    {

        return $this->_objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Collection');

    }



    /**

     * @return float

     */

    public function getCountOrder()

    {

        $orderCollection = $this->getCollection([Type::QUEUE_SALESORDER]);

		//echo $orderCollection->count();die;

        return (ceil(($orderCollection->count()) / Limited::LIMITED_ORDER));



    }



    /**

     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection|mixed

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

			$sql = "Select main_table.entity_id, main_table.increment_id, main_table.customer_id, e.entity_id as ecustomer_id FROM " . $tableName." as main_table left join ".$tableName2." as e on e.entity_id = main_table.customer_id where e.entity_id is not NULL";

			$result = $connection->fetchAll($sql);

			foreach($result as $cust){

				$allIds[] = $cust['entity_id'];

			}

			

			$idToQueue = array_diff($allIds, $allNullProductsOrder);

			return $idToQueue;

    }

    /**

     * @return float

     */

    public function getCountInvoice()

    {

        $invoiceCollection = $this->getCollection([Type::QUEUE_INVOICE, Type::QUEUE_RECEIVEPAYMENT]);

		//echo $invoiceCollection->count();die;

        return (ceil(($invoiceCollection->count()) / Limited::LIMITED_INVOICE));

    }



    /**

     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection|mixed

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

		/*$sql5 = "Select * FROM magenest_qbd_queue where company_id = 3 and status = 3 and action_name = 'SalesOrderAdd'";

		$result5 = $connection->fetchAll($sql5);

		foreach($result5 as $queue){

			$failedQueue[] = $queue['entity_id'];

		}*/
		$failedQueue = array();
		if(!empty($failedQueue)){
			
		
			$quoeueFailed = implode(',',$failedQueue);

			$sql = "Select main_table.entity_id, main_table.increment_id, main_table.customer_id, e.entity_id as ecustomer_id FROM " . $tableName." as main_table left join ".$tableName2." as e on e.entity_id = main_table.customer_id where e.entity_id is not NULL and main_table.entity_id NOT IN ('".$quoeueFailed."')";
		}else{
			$sql = "Select main_table.entity_id, main_table.increment_id, main_table.customer_id, e.entity_id as ecustomer_id FROM " . $tableName." as main_table left join ".$tableName2." as e on e.entity_id = main_table.customer_id where e.entity_id is not NULL";
		}
		//echo $sql;die;

		$result = $connection->fetchAll($sql);

		foreach($result as $cust){

			$allIds[] = $cust['entity_id'];

		}



		$orderIds = array_diff($allIds, $allNullProductsOrder);
		//echo count($orderIds);die;
        $invoiceCollection = $this->_objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Invoice\Collection')->addFieldToFilter('order_id', array('IN' => $orderIds));
		//echo count($invoiceCollection);die;
		return $invoiceCollection;

    }



    /**

     * @return float

     */

    public function getCountCreditMemo()

    {

        $memoCollection = $this->getCollection([Type::QUEUE_CREDITMEMO]);

        return (ceil(($memoCollection->count()) / Limited::LIMITED_CREDITMEMO));



    }



    /**

     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection|mixed

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

