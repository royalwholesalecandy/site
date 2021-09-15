<?php namespace BoostMyShop\UltimateReport\Block;

abstract class AbstractContainer extends \Magento\Backend\Block\Template {

    protected $_template = 'AbstactContainer.phtml';

    protected $_storeCollectionFactory = null;
    protected $_coreRegistry;
    protected $_ultimateReportRegistry;

    protected $_hidden = [];

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \BoostMyShop\UltimateReport\Model\Registry $ultimateReportRegistry,
        array $data = [])
    {
        parent::__construct($context, $data);

        $this->_storeCollectionFactory = $storeCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_ultimateReportRegistry = $ultimateReportRegistry;

        $this->_configure();
    }

    abstract protected function _configure();
    abstract public function getPageCode();

    public function getReportUrl()
    {
        return $this->getUrl('ultimatereport/page/index');
    }


    public function showFilter($type)
    {
        $this->setData('show_filter_'.$type, true);
        return $this;
    }

    public function canShowFilter($type)
    {
        if ($this->getData('show_filter_'.$type))
            return true;
        return false;
    }

    public function getStoreFilterOptions()
    {
        $collection = $this->_storeCollectionFactory->create();

        $allStoreIds = [];
        foreach($collection as $store)
            $allStoreIds[] = $store->getId();

        $options = [];
        $options[implode(',', $allStoreIds)] = __('All');

        foreach($collection as $store)
        {
            $options[$store->getId()] = $store->getWebsite()->getName().' > '.$store->getGroup()->getName().' > '.$store->getName();
        }
        return $options;
    }

    public function getGroupByDateOptions()
    {
        $options = [];
        $options['%d %b %y %h %p'] = __('Hour');
        $options['%d %b %y'] = __('Day');
        $options['%v %Y'] = __('Week');
        $options['%b %Y'] = __('Month');
        $options['%Y'] = __('Year');
        return $options;
    }

    public function getIntervalOptions()
    {
        $options = [];

        $options['today'] = __('Today');
        $options['yesterday'] = __('Yesterday');
        $options['current_month'] = __('This month');
        $options['last_month'] = __('Last month');
        $options['last_month_3'] = __('Last 3 month');
        $options['last_month_6'] = __('Last 6 month');
        $options['current_year'] = __('This year');
        $options['last_year'] = __('Last year');
        $options['lifetime'] = __('Lifetime');
        $options['custom'] = __('Custom');

        return $options;
    }

    public function addHiddenField($id, $value)
    {
        $this->_hidden[$id] = $value;
    }

    public function getHiddenfields()
    {
        return $this->_hidden;
    }

    public function getFilterValue($code)
    {
        $filters = $this->_ultimateReportRegistry->getFilters();
        if (isset($filters[$code]))
            return $filters[$code];
        else
            return false;
    }

}