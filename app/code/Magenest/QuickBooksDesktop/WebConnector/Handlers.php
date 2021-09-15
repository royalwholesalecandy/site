<?php
namespace Magenest\QuickBooksDesktop\WebConnector;
use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;
use Magenest\QuickBooksDesktop\Helper\GenerateTicket;
use Magenest\QuickBooksDesktop\Helper\Result;
use Magenest\QuickBooksDesktop\Model\MappingFactory;
use Magenest\QuickBooksDesktop\Model\Ticket;
use Magenest\QuickBooksDesktop\WebConnector\Receive\Response as ReceiveResponse;
use Magento\Framework\ObjectManagerInterface;
abstract class Handlers

{

	/**

	 * @var QueueHelper

	 */

	protected $_queueHelper;



	/**

	 * @var Driver

	 */

	protected $_driver;



	/**

	 * @var GenerateTicket

	 */

	protected $_generateTicket;



	/**

	 * @var ReceiveResponse

	 */

	protected $receiveResponse;



	/**

	 * @var ObjectManagerInterface

	 */

	protected $_objectManager;



	/**

	 * @var MappingFactory

	 */

	protected $_mapping;



	/**

	 * Handlers constructor.

	 *

	 * @param GenerateTicket $generateTicket

	 * @param Ticket $ticket

	 * @param ReceiveResponse $receiveResponse

	 * @param ObjectManagerInterface $objectManager

	 */

	public function __construct(

		GenerateTicket $generateTicket,

		Ticket $ticket,

		ReceiveResponse $receiveResponse,

		ObjectManagerInterface $objectManager,

		MappingFactory $mappingFactory,

		QueueHelper $queueHelper

	) {

		$this->_generateTicket = $generateTicket;

		$this->receiveResponse = $receiveResponse;

		$this->_ticket = $ticket;

		$this->_objectManager = $objectManager;

		$this->_mapping = $mappingFactory;

		$this->_queueHelper = $queueHelper;

	}



	/**

	 * Check username and password send from QB Web Connector

	 *

	 * @param \stdClass $obj

	 * @return Result\Authenticate

	 */

	public function authenticate($obj)

	{

		/** Default Value */

		$status = 'nvu';

		$ticketCode = '88888-88888-88888-88888';



		if ($this->_driver->authenticate($obj)) {

			$status = 'none';

			$processed = $this->getProcessQueue();

			if ($processed) {

				$status = '';

			} else {

				$processed = 0;

			}

			$ticketCode = $this->_generateTicket->generateTicket($obj->strUserName, $processed);

		}



		return new Result\Authenticate($ticketCode, $status);

	}



	/**

	 * Get Process Queue

	 *

	 * @return bool|int

	 */

	protected function getProcessQueue()

	{

		$processed = $this->_driver->getTotalsQueue();



		return $processed;

	}



	/**

	 * Magento Version

	 *

	 * @return Result\ServerVersion

	 */

	public function serverVersion()

	{

		/** @var \Magenest\QuickBooksDesktop\Helper\Result\ServerVersion $serverVersion */

		$serverVersion = $this->_objectManager->create('Magenest\QuickBooksDesktop\Helper\Result\ServerVersion');



		return $serverVersion;

	}



	/**

	 * Get and check QB Web Connector version

	 *

	 * @return Result\ClientVersion

	 */

	public function clientVersion()

	{

		return new Result\ClientVersion;

	}



	/**

	 * Close Connection

	 *

	 * @return Result\CloseConnection

	 */

	public function closeConnection()

	{

		return new Result\CloseConnection;

	}



	/**

	 * Send Request to QB Web Connector

	 *

	 * @param \stdClass $dataFromQWC

	 * @return Result\SendRequestXML

	 */

	public function sendRequestXML($dataFromQWC)

	{

		$queue = $this->getCurrentQueue($dataFromQWC);

		if($queue){

			$xml = $this->prepareXml($queue);

			return new Result\SendRequestXML($xml);

		}

		

	}



	/**

	 * Get Current Queue

	 *

	 * @param \stdClass $dataFromQWC

	 * @return \Magenest\QuickBooksDesktop\Model\Queue

	 */

	protected function getCurrentQueue($dataFromQWC)

	{

		$ticket = $this->_ticket->loadByCode($dataFromQWC->ticket);

		$current = ((int)$ticket->getCurrent()) + 1;

		$ticket->setCurrent($current);

		$ticket->save();



		return $this->_driver->getCurrentQueue();

	}



	/**

	 * Prepare Xml

	 *

	 * @param \Magenest\QuickBooksDesktop\Model\Queue $queue

	 * @return string

	 */

	protected function prepareXml($queue)

	{

		

		

		$xml = $this->_driver->prepareSendRequestXML($queue);



		return $xml;

	}



	/**

	 * @return ReceiveResponse

	 */

	protected function getReceiveResponse()

	{

		return $this->receiveResponse;

	}



	/**

	 * @param $dataFromQWC

	 */

	protected function processResponse($dataFromQWC)

	{

		\Magento\Framework\App\ObjectManager::getInstance()

			->get('Magenest\QuickBooksDesktop\Logger\Logger')->debug("Start Process Response1");

		$response = $this->getReceiveResponse();


		\Magento\Framework\App\ObjectManager::getInstance()

			->get('Magenest\QuickBooksDesktop\Logger\Logger')->debug("Start Process Response2");

		$response->setResponse($dataFromQWC);

		\Magento\Framework\App\ObjectManager::getInstance()

			->get('Magenest\QuickBooksDesktop\Logger\Logger')->debug("Start Process Response3");

		$response->getAttribute();

		\Magento\Framework\App\ObjectManager::getInstance()

			->get('Magenest\QuickBooksDesktop\Logger\Logger')->debug("Start Process Response4");

	}



	/**

	 * @return \Psr\Log\LoggerInterface

	 */

	protected function logger()

	{

		return $this->_objectManager->create('Psr\Log\LoggerInterface');

	}



	/**

	 * Receive Response XML

	 *

	 * @param \stdClass $dataFromQWC

	 * @return Result\ReceiveResponseXML

	 */

	public function receiveResponseXML($dataFromQWC)

	{

		$this->processResponse($dataFromQWC);

		$model = $this->_ticket->loadByCode($dataFromQWC->ticket);

		$totals = (int)$model->getProcessed();

		$current = (int)$model->getCurrent();

		$percent = (int)($current * 100 / $totals);



		return new Result\ReceiveResponseXML($percent);

	}



	/**

	 * Get Last Error

	 *

	 * @param $obj

	 * @return Result\GetLastError

	 */

	public function getLastError($obj)

	{

		return new Result\GetLastError($obj->response);

	}

	/**
	 * 2019-12-14 Dmitry Fedyuk https://github.com/mage2pro
	 * `Magenest_QuickBooksDesktop`: «Function 'connectionError' doesn't exist»
	 * in vendor/magento/zendframework1/library/Zend/Soap/Server.php:
	 * https://github.com/royalwholesalecandy/core/issues/36
	 */
	function connectionError() {}
}