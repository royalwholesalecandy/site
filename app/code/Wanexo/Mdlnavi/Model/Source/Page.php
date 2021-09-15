<?php
namespace Wanexo\Mdlnavi\Model\Source;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;

/**
 * Catalog category landing page attribute source
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Page extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Page collection factory
     *
     * @var CollectionFactory
     */
    protected $_pageCollectionFactory;

    /**
     * Construct
     *
     * @param CollectionFactory $pageCollectionFactory
     */
    public function __construct(CollectionFactory $pageCollectionFactory)
    {
        $this->_pageCollectionFactory = $pageCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->_pageCollectionFactory->create()->load()->toOptionArray();
            array_unshift($this->_options, ['value' => '', 'label' => __('Please select a page.')]);
        }
        return $this->_options;
    }
}
