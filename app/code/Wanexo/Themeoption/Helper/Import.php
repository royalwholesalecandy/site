<?php
namespace Wanexo\Themeoption\Helper;
use Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Module\Dir;

class Import extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
	 * @var \Wanexo\Themeoption\Helper\Data
	 */
	protected $_wanexoData;

	/**
	 * @param \Magento\Framework\App\Helper\Context     $context  
	 * @param \Magento\Framework\App\ResourceConnection $resource 
	 * @param \Wanexo\Themeoption\Helper\Data                    $wanexoData  
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\App\ResourceConnection $resource,
		\Wanexo\Themeoption\Helper\Data $wanexoData
		) {
		parent::__construct($context);
		$this->_resource = $resource;
		$this->_wanexodata = $wanexoData;
	}

	public function buildQueryImport($data = array(), $table_name = "", $override = true, $store_id = 0, $where = '') {
		$query = false;
		$binds = array();
		if($data) {
			$table_name = $this->_resource->getTableName($table_name);
			if($override) {
				$query = "REPLACE INTO `".$table_name."` ";
			} else {
				$query = "INSERT IGNORE INTO `".$table_name."` ";
			}
			$stores = $this->_wanexodata->getAllStores();
			$fields = $values = array();
			foreach($data as $key=>$val) {
				if($val) {
					if($key == "store_id" && !in_array($val, $stores)){
						$val = $store_id;
					}
					$fields[] = "`".$key."`";
					$values[] = ":".strtolower($key);
					$binds[strtolower($key)] = $val;
				}
			}
			$query .= " (".implode(",", $fields).") VALUES (".implode(",", $values).")";
		}
		return array($query, $binds);
	}
}