<?php
namespace Wanexo\Mlayer\Controller\Adminhtml\Banner;

use Wanexo\Mlayer\Model\Banner;
class MassDelete extends MassAction
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 record(s) have been deleted';
    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while deleting record(s).';

    /**
     * @param $banner
     * @return $this
     */
    protected function doTheAction(Banner $banner)
    {
        $banner->delete();
        return $this;
    }
}