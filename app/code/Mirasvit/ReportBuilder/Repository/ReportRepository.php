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

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\ReportBuilder\Api\Data\ReportInterface;
use Mirasvit\ReportBuilder\Api\Repository\ReportRepositoryInterface;
use Mirasvit\ReportBuilder\Api\Data\ReportInterfaceFactory;
use Mirasvit\ReportBuilder\Model\ResourceModel\Report\CollectionFactory;
use Magento\Backend\Model\Auth\Session;

class ReportRepository implements ReportRepositoryInterface
{
    private $entityManager;

    private $factory;

    private $collectionFactory;

    private $authSession;

    public function __construct(
        EntityManager $entityManager,
        ReportInterfaceFactory $factory,
        CollectionFactory $collectionFactory,
        Session $authSession
    ) {
        $this->entityManager = $entityManager;
        $this->factory = $factory;
        $this->collectionFactory = $collectionFactory;
        $this->authSession = $authSession;
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
    public function save(ReportInterface $report)
    {
        return $this->entityManager->save($report);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ReportInterface $report)
    {
        $this->entityManager->delete($report);

        return $this;
    }

    public function getUserId()
    {
        return $this->authSession->getUser() ? $this->authSession->getUser()->getId() : 0;
    }
}
