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
 * @package   mirasvit/module-report-builder
 * @version   1.0.24
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportBuilder\Repository;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\Data\BookmarkInterface;
use Mirasvit\ReportBuilder\Api\Data\ConfigInterface;
use Mirasvit\ReportBuilder\Api\Repository\ConfigRepositoryInterface;
use Mirasvit\ReportBuilder\Api\Data\ConfigInterfaceFactory;
use Mirasvit\ReportBuilder\Model\ResourceModel\Config\CollectionFactory;
use Magento\Backend\Model\Auth\Session;

class ConfigRepository implements ConfigRepositoryInterface
{
    const REPORT_BOOKMARK = 'mst_report_';

    private $entityManager;

    private $factory;

    private $collectionFactory;

    private $authSession;

    /**
     * @var BookmarkRepositoryInterface
     */
    private $bookmarkRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var TypeListInterface
     */
    private $typeList;

    public function __construct(
        TypeListInterface $typeList,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BookmarkRepositoryInterface $bookmarkRepository,
        EntityManager $entityManager,
        ConfigInterfaceFactory $factory,
        CollectionFactory $collectionFactory,
        Session $authSession
    ) {
        $this->entityManager = $entityManager;
        $this->factory = $factory;
        $this->collectionFactory = $collectionFactory;
        $this->authSession = $authSession;
        $this->bookmarkRepository = $bookmarkRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->typeList = $typeList;
    }

    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    public function create()
    {
        return $this->factory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $model = $this->create();
        $this->entityManager->load($model, $id);

        return $model->getId() ? $model : false;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ConfigInterface $config)
    {
        $this->cleanCache();
        $this->typeList->cleanType('config');

        return $this->entityManager->save($config);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ConfigInterface $config)
    {
        $this->entityManager->delete($config);

        $this->cleanCache();

        return $this;
    }

    public function getUserId()
    {
        return $this->authSession->getUser() ? $this->authSession->getUser()->getId() : 0;
    }

    /**
     * Report bookmarks and config cache should be removed
     * when a new config is added to refresh the columns.
     */
    private function cleanCache()
    {
        // clean config cache
        $this->typeList->cleanType('config');

        // remove bookmarks
        $this->searchCriteriaBuilder->addFilter(BookmarkInterface::BOOKMARKSPACE, self::REPORT_BOOKMARK. '%', 'like');
        $bookmarks = $this->bookmarkRepository->getList($this->searchCriteriaBuilder->create());
        foreach ($bookmarks->getItems() as $bookmark) {
            $this->bookmarkRepository->deleteById($bookmark->getId());
        }
    }
}
