<?php

namespace Magewares\MWQuickOrder\Block;

class Header extends \Magento\Framework\View\Element\Html\Link
{
	protected $_template = 'Magewares_MWQuickOrder::link.phtml';

	public function getLabel()
	{
		return __('Quick Order');
	}
}