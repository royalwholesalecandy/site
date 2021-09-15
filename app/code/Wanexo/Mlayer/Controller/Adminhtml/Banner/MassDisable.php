<?php
namespace Wanexo\Mlayer\Controller\Adminhtml\Banner;

use Wanexo\Mlayer\Model\Banner;
class MassDisable extends MassAction
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 banners have been disabled';
    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while disabling banners.';
    /**
     * @var bool
     */
    protected $isActive = false;

    /**
     * @param Banner $banner
     * @return $this
     */
    protected function doTheAction(Banner $banner)
    {
        $banner->setIsActive($this->isActive);
        $banner->save();
        return $this;
    }
}
