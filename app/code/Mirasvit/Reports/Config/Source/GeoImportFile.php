<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-reports
 * @version   1.3.31
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Reports\Config\Source;

use Mirasvit\Reports\Model\Config;

class GeoImportFile
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toOptionArray()
    {
        $result = [];

        $url = Config::GEO_FILE_URL;
        $json = @json_decode(@file_get_contents($url), true);

        if (is_array($json)) {
            foreach ($json['data'] as $entity) {
                $result[] = [
                    'label' => $entity["name"],
                    'value' => 'http://files.mirasvit.com/report/postcode/download/?identifier=' . $entity["identifier"],
                ];
            }
        }

        asort($result);

        return $result;
    }
}
