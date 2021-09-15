<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-sphinx
 * @version   1.1.40
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchSphinx\Adapter;

use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder as MysqlAggregationBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use Magento\Framework\Search\Adapter\Mysql\ResponseFactory as MysqlResponseFactory;
use Magento\Framework\Search\Adapter\Mysql\DocumentFactory as MysqlDocumentFactory;
use Magento\Framework\App\ObjectManager;
use Mirasvit\Search\Model\Config as SearchConfig;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Adapter implements AdapterInterface
{
    /**
     * Mapper instance
     *
     * @var Mapper
     */
    protected $mapper;

    /**
     * Response Factory
     *
     * @var MysqlResponseFactory
     */
    protected $responseFactory;

    /**
     * @var MysqlAggregationBuilder
     */
    private $aggregationBuilder;

    /**
     * @var TemporaryStorageFactory
     */
    protected $temporaryStorageFactory;

    /**
     * @var SearchConfig
     */
    protected $searchConfig;

    public function __construct(
        Mapper $mapper,
        MysqlResponseFactory $responseFactory,
        MysqlAggregationBuilder $aggregationBuilder,
        TemporaryStorageFactory $temporaryStorageFactory,
        SearchConfig $searchConfig,
        MysqlDocumentFactory $documentFactory,
        ResourceConnection $resource
    ) {
        $this->mapper = $mapper;
        $this->responseFactory = $responseFactory;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->searchConfig = $searchConfig;
        $this->documentFactory = $documentFactory;
        $this->resource = $resource;
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\Search\Response\QueryResponse
     */
    public function query(RequestInterface $request)
    {
        try {
            $query = $this->mapper->buildQuery($request);
            $query->limit($this->searchConfig->getResultsLimit());
        } catch (\Exception $e) {
            // fallback engine
            $objectManager = ObjectManager::getInstance();

            return $objectManager->create('Mirasvit\SearchMysql\Adapter\Adapter')
                ->query($request);
        }

        $temporaryStorage = $this->temporaryStorageFactory->create();
        $table = $temporaryStorage->storeDocumentsFromSelect($query);

        return $this->responseFactory->create([
            'documents'    => $this->getDocuments($table),
            'aggregations' => $this->aggregationBuilder->build($request, $table),
        ]);
    }

    /**
     * @param \Magento\Framework\DB\Ddl\Table $table
     * @return array
     * @throws \Zend_Db_Exception
     */
    private function getDocuments(\Magento\Framework\DB\Ddl\Table $table)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($table->getName(), ['entity_id', 'score']);

        return $connection->fetchAssoc($select);
    }

    /**
     * @return false|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
