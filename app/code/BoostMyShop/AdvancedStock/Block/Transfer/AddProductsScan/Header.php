<?php namespace BoostMyShop\AdvancedStock\Block\Transfer\AddProductsScan;

/**
 * Class Header
 *
 * @package   BoostMyShop\AdvancedStock\Block\Transfer\AddProductsScan
 * @author    Romain Jourdes <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Header extends \Magento\Backend\Block\Template
{
    protected $_template = 'AdvancedStock/Transfer/AddProductsScan/Header.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Header constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/edit', ['id' => $this->getTransfer()->getId()]);
    }

    /**
     * @return \BoostMyShop\AdvancedStock\Model\Transfer
     */
    public function getTransfer()
    {
        return $this->_coreRegistry->registry('current_transfer');
    }
}
