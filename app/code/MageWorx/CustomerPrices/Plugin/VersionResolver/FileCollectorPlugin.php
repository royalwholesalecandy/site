<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\CustomerPrices\Plugin\VersionResolver;

use Magento\Framework\View\File\Collector\Decorator\ModuleDependency;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\App\ProductMetadataInterface;

class FileCollectorPlugin
{
    /**
     * @var ProductMetadataInterface $productMetadata
     */
    public $productMetadata;

    /**
     * FileCollectorPlugin constructor.
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ProductMetadataInterface $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
    }


    /**
     * @param ModuleDependency $subject
     * @param callable $proceed
     * @param ThemeInterface $theme
     * @param string $filePath
     * @return array
     */
    public function aroundGetFiles(ModuleDependency $subject, callable $proceed, ThemeInterface $theme, $filePath)
    {
        if ($filePath == 'product_form.xml' && $this->isExcludeFile()) {

            $result = $proceed($theme, $filePath);

            if (is_array($result)) {
                $modResult = [];
                /**
                 * @var \Magento\Framework\View\File $file
                 */
                foreach ($result as $file) {
                    if ($file->getFileIdentifier() == '|module:MageWorx_CustomerPrices|file:product_form.xml') {
                        continue;
                    }
                    $modResult[] = $file;
                }

                return $modResult;
            }
        }

        return $proceed($theme, $filePath);
    }

    /**
     * @return bool
     */
    protected function isExcludeFile()
    {
        return  version_compare($this->productMetadata->getVersion(), '2.2.0-dev', '<');
    }

}