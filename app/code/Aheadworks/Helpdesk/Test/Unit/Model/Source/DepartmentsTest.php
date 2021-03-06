<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Test\Unit\Model\Source;

use Aheadworks\Helpdesk\Model\Source\Departments;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\CollectionFactory;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\Collection as DepartmentCollection;
use Aheadworks\Helpdesk\Model\Department as DepartmentModel;

/**
 * Test for \Aheadworks\Helpdesk\Model\Source\Departments
 */
class DepartmentsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Departments
     */
    private $sourceModel;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $departmentCollectionFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->departmentCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->sourceModel = $objectManager->getObject(
            Departments::class,
            [
                'departmentCollectionFactory' => $this->departmentCollectionFactoryMock
            ]
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $departmentId = 1;
        $departmentName = 'Test department';

        $departmentModelMock = $this->getMockBuilder(DepartmentModel::class)
            ->setMethods(['getId', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $departmentModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($departmentId);
        $departmentModelMock->expects($this->once())
            ->method('getName')
            ->willReturn($departmentName);

        $collectionMock = $this->getMockBuilder(DepartmentCollection::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$departmentModelMock]));

        $this->departmentCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);

        $options = [
            ['label' => $departmentName, 'value' => $departmentId]
        ];
        $this->assertEquals($options, $this->sourceModel->toOptionArray());
    }

    /**
     * Test getOptions method
     */
    public function testGetOptions()
    {
        $departmentId = 1;
        $departmentName = 'Test department';

        $departmentModelMock = $this->getMockBuilder(DepartmentModel::class)
            ->setMethods(['getId', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $departmentModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($departmentId);
        $departmentModelMock->expects($this->once())
            ->method('getName')
            ->willReturn($departmentName);

        $collectionMock = $this->getMockBuilder(DepartmentCollection::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$departmentModelMock]));

        $this->departmentCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);

        $options = [
            $departmentId => $departmentName
        ];

        $this->assertEquals($options, $this->sourceModel->getOptions());
    }

    /**
     * Test getOptionByValue method
     */
    public function testGetOptionByValue()
    {
        $departmentId = 1;
        $departmentName = 'Test department';

        $departmentModelMock = $this->getMockBuilder(DepartmentModel::class)
            ->setMethods(['getId', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $departmentModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($departmentId);
        $departmentModelMock->expects($this->once())
            ->method('getName')
            ->willReturn($departmentName);

        $collectionMock = $this->getMockBuilder(DepartmentCollection::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$departmentModelMock]));

        $this->departmentCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);

        $this->assertEquals($departmentName, $this->sourceModel->getOptionByValue($departmentId));
    }
}
