<?php
namespace Wanexo\Brand\Controller\Adminhtml\Brandaction;

use Wanexo\Brand\Model\Brand;

class MassDisable extends MassAction
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 brands have been disabled';
    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while disabling brands.';
    /**
     * @var bool
     */
    protected $status = false;

    protected function doTheAction(Brand $brand)
    {
        $brand->setStatus($this->status);
        $brand->save();
        return $this;
    }
}
