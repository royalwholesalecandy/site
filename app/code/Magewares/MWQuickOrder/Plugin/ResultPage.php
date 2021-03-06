<?php
namespace Magewares\MWQuickOrder\Plugin;

class ResultPage
{

    /**
     * Adding the default catalog_product_view handles as well
     */
    public function beforeAddPageLayoutHandles(
        \Magento\Framework\View\Result\Page $subject,
        array $parameters = [],
        $defaultHandle = null)
    {

        $arrayKeys = array_keys($parameters);
        if ((count($arrayKeys) == 3) &&
                in_array('id', $arrayKeys) &&
                in_array('sku', $arrayKeys) &&
                in_array('type', $arrayKeys)) {

            return [$parameters, 'catalog_product_view'];
        }
    }

}
