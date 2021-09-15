<?php
namespace Wanexo\Themeoption\Model\System\Config\Source\Export;

class FileExtension
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toArray()
    {
        $extension = [];
        $extension['.json'] = __('.json');
        return $extension;
    }
}