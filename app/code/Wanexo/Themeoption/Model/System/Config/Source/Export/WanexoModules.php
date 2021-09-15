<?php
namespace Wanexo\Themeoption\Model\System\Config\Source\Export;
use Magento\Framework\App\Filesystem\DirectoryList;

class WanexoModules implements \Magento\Framework\Option\ArrayInterface
{
	/**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;
    /**
     * @param \Magento\Cms\Model\Block $blockModel
     */
    public function __construct(
        \Magento\Framework\Module\ModuleListInterface $moduleList
        ) {
    	$this->_moduleList = $moduleList;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $output = [];
        $modules = $this->_moduleList->getNames();
        sort($modules);
        foreach ($modules as $k => $v) {
            if(preg_match("/Wanexo/", $v)){
                $output[$k] = [
                'value' => $v,
                'label' => $v
                ];
            }
        }
        return $output;
    }

    protected function getInstallConfig()
    {
        $file = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('etc/config.php');
        $installConfig = include $file;
        return $installConfig;
    }
}