<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Plugin;

use Magento\Framework\Convert\Excel;
use Magento\Framework\Convert\ExcelFactory;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;
use Amasty\Segments\Controller\Adminhtml\Segment;

abstract class AbstractExport
{
    const CUSTOMER_COMPONENT_NAMESPACE = 'amastysegments_customer_listing';

    const GUEST_COMPONENT_NAMESPACE = 'amastysegments_guest_listing';

    /**
     * @var string
     */
    protected $exportType = '';

    /**
     * @var \Amasty\Segments\Helper\Base
     */
    private $baseHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var WriteInterface
     */
    protected $directory;

    /**
     * @var \Magento\Ui\Model\Export\MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var ExcelFactory
     */
    protected $excelFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Magento\Ui\Model\Export\SearchResultIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var int|null
     */
    protected $pageSize = null;

    /**
     * @var \Amasty\Segments\Model\SegmentRepository
     */
    protected $segmentRepository;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\Context
     */
    protected $context;

    /**
     * AbstractExport constructor.
     * @param \Amasty\Segments\Helper\Base $baseHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param \Magento\Ui\Model\Export\MetadataProvider $metadataProvider
     * @param ExcelFactory $excelFactory
     * @param \Magento\Ui\Model\Export\SearchResultIteratorFactory $iteratorFactory
     * @param int $pageSize
     */
    public function __construct(
        \Amasty\Segments\Helper\Base $baseHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Amasty\Segments\Model\SegmentRepository $segmentRepository,
        \Magento\Framework\View\Element\UiComponent\Context $context,
        Filesystem $filesystem,
        Filter $filter,
        \Magento\Ui\Model\Export\MetadataProvider $metadataProvider,
        ExcelFactory $excelFactory,
        \Magento\Ui\Model\Export\SearchResultIteratorFactory $iteratorFactory,
        $pageSize = 200
    ) {
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->excelFactory = $excelFactory;
        $this->iteratorFactory = $iteratorFactory;
        $this->pageSize = $pageSize;
        $this->baseHelper = $baseHelper;
        $this->segmentRepository = $segmentRepository;
        $this->request = $request;
        $this->context = $context;
    }

    /**
     * @return bool
     */
    public function checkNamespace()
    {
        $namespace = $this->request->getParam('namespace');

        return (!in_array($namespace, [self::CUSTOMER_COMPONENT_NAMESPACE, self::GUEST_COMPONENT_NAMESPACE]));
    }

    /**
     * @return array
     */
    protected function exportByType()
    {
        $this->baseHelper->initCurrentSegment(
            $this->segmentRepository->get($this->request->getParam(Segment::SEGMENT_PARAM_URL_KEY, 0))
        );

        switch ($this->exportType) {
            case 'csv':
                return $this->getCsvFile();
                break;
            case 'xml':
                return $this->getXmlFile();
                break;
        }
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCsvFile()
    {
        $component = $this->filter->getComponent();

        $name = md5(microtime());
        $file = 'export/'. $component->getName() . $name . '.csv';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        $fields = $this->metadataProvider->getFields($component);
        $options = $this->metadataProvider->getOptions();

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->metadataProvider->getHeaders($component));
        $i = 1;
        $searchCriteria = $dataProvider->getSearchCriteria()
            ->setCurrentPage($i)
            ->setPageSize($this->pageSize);

        $dataProvider->getCollection()->setIsExport(true);
        $dataSource['data']['items'] = $dataProvider->getData()['items'];
        $items = $this->prepareDataSource($dataSource, $component);

        foreach ($items as $item) {
            $this->metadataProvider->convertDate($item, $component->getName());
            $stream->writeCsv($this->getRowData($item, $fields, $options));
        }

        $searchCriteria->setCurrentPage(++$i);

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true
        ];
    }

    /**
     * @return array
     */
    public function getXmlFile()
    {
        $component = $this->filter->getComponent();

        $name = md5(microtime());
        $file = 'export/'. $component->getName() . $name . '.xml';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();

        $component->getContext()->getDataProvider()->setLimit(0, 0);
        $dataProvider = $component->getContext()->getDataProvider();
        $dataProvider->getCollection()->setIsExport(true);
        $dataSource['data']['items'] = $dataProvider->getData()['items'];
        $searchResultItems = $this->prepareDataSource($dataSource, $component);

        /** @var SearchResultIterator $searchResultIterator */
        $searchResultIterator = $this->iteratorFactory->create(['items' => $searchResultItems]);

        /** @var Excel $excel */
        $excel = $this->excelFactory->create([
            'iterator' => $searchResultIterator,
            'rowCallback'=> [$this, 'getRowXmlData'],
        ]);

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        $excel->setDataHeader($this->metadataProvider->getHeaders($component));
        $excel->write($stream, $component->getName() . '.xml');

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true
        ];
    }

    /**
     * @param array $data
     * @param \Magento\Framework\View\Element\UiComponentInterface $component
     * @return array
     */
    protected function prepareDataSource(array & $data, \Magento\Framework\View\Element\UiComponentInterface $component)
    {
        $childComponents = $component->getChildComponents();
        if (!empty($childComponents)) {
            foreach ($childComponents as $child) {
                $this->prepareDataSource($data, $child);
            }
        }
        $data = $component->prepareDataSource($data);

        return $data['data']['items'];
    }

    /**
     * @param $item
     * @return array
     */
    public function getRowXmlData($item) {
        $fields = $this->metadataProvider->getFields($this->filter->getComponent());
        $options = $this->metadataProvider->getOptions();

        return $this->getRowData($item, $fields, $options);
    }

    /**
     * @param $item
     * @param $fields
     * @param $options
     * @return array
     */
    protected function getRowData($item, $fields, $options)
    {
        $row = [];
        foreach ($fields as $column) {
            if (array_key_exists($column, $item) && $item[$column]) {
                $row[] = $item[$column];
            } else {
                $row[] = '-';
            }
        }

        return $row;
    }
}
