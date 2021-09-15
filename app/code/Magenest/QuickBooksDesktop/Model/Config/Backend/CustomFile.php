<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Model\Config\Backend;

/**
 * Class Filter
 * @package Magenest\QuickBooksDesktop\Model\Config\Source
 */
class CustomFile extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * @return string[]
     */
    public function getAllowedExtensions()
    {
        return ['csv'];
    }
}
