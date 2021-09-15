<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing;

class ProductCustomOptions extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $data = $this->getRequest()->getPost();


        $result = [];

        try
        {
            $productId = $data['product_id'];
            $product = $this->_productFactory->create()->load($productId);

            $result['success'] = true;
            $result['html'] = $this->getOptionsAsHtml($product);
        }
        catch(\Exception $ex)
        {
            $result['success'] = false;
            $result['message'] = $ex->getMessage();
            $result['stack'] = $ex->getTraceAsString();
        }

        die(json_encode($result));
    }

    protected function getOptionsAsHtml($product)
    {
        $html = '<table border="0">';

        foreach($product->getOptions() as $option)
        {
            $html .= '<tr>';
            $html .= '<td valign="top"><b>'.$option->getTitle().' </b> </td>';
            $html .= '<td>';

            $html .= $this->getOptionForm($option);

            $html .= '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }

    protected function getOptionForm($option)
    {
        $html = '';
        switch($option->getType())
        {
            case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_RADIO;
                foreach($option->getValues() as $optionValue)
                {
                    $html .= '<input type="radio" name="options['.$option->getId().']" value="'.$optionValue->getId().'"> '.$optionValue->getTitle().'<br>';
                }
                break;
            case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_CHECKBOX;
                foreach($option->getValues() as $optionValue)
                {
                    $html .= '<input type="checkbox" name="options['.$option->getId().']" value="'.$optionValue->getId().'"> '.$optionValue->getTitle().'<br>';
                }
                break;
            default:
                $html .= 'Option type '.$option->getType().' is not supported';
                break;
        }

        return $html;
    }

}
