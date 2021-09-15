<?php
namespace Wanexo\Themeoption\Model\System\Config\Source\Export;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportFolders implements \Magento\Framework\Option\ArrayInterface
{
	protected  $_blockModel;

    /**
     * @var \Magento\Theme\Model\Theme
     */
    protected $_collectionThemeFactory;

    /**
     * File system
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @param \Magento\Cms\Model\Block $blockModel
     */
    public function __construct(
    	\Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $collectionThemeFactory,
        \Magento\Framework\Filesystem $filesystem
        ) {
    	$this->_collectionThemeFactory = $collectionThemeFactory;
        $this->_filesystem = $filesystem;
    }

    public function toOptionArray(){
        $themes = $this->_collectionThemeFactory->create();
        $file = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('design/frontend/');
        $wanexoPackagePaths = glob($file . '*/*/config.xml');
        $output = [];
        foreach ($wanexoPackagePaths as $k => $v) {
            $v = str_replace("/config.xml", "", $v);
            $output[] = [
                'label' => ucfirst(str_replace($file, "", $v)),
                'value' => str_replace($file, "", $v)
                ];
        }
        return $output;
    }

    public function toArray(){
        $themes = $this->_collectionThemeFactory->create();
        $file = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('design/frontend/');
        $wanexoPackagePaths = glob($file . '*/*/config.xml');
        $output = [];
        foreach ($wanexoPackagePaths as $k => $v) {
            $v = str_replace("/config.xml", "", $v);
            $output[str_replace($file, "", $v)] = ucfirst(str_replace($file, "", $v));
        }
        return $output;
    }
}