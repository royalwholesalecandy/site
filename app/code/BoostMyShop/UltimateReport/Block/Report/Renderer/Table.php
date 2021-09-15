<?php namespace BoostMyShop\UltimateReport\Block\Report\Renderer;

class Table extends AbstractRenderer {

    protected $_template = 'Reports/Renderer/Table.phtml';

    public function getColumns()
    {
        $columns = array();
        $columnsNode = (array)$this->getReport()->getTable()->children()[0];
        foreach($columnsNode as $key => $node)
        {
            $columns[$key] = (array)$node;
        }
        return $columns;
    }

    public function applyRenderer($column, $key, $row)
    {
        $html = '';
        $renderer = isset($column['renderer']) ? $column['renderer'] : '';
        switch($renderer)
        {
            case 'link':
                $link = $this->getUrl($column['url'], [$column['param_name'] => $row[$column['param_value']]]);
                $html = '<a href="'.$link.'">'.$row[$key].'</a>';
                break;
            default:
                $html = $row[$key];
                break;

        }

        return $html;
    }

}
