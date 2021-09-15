<?php
namespace Wanexo\Mlayer\Controller\Adminhtml\Banner;

class MassEnable extends MassDisable
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 banners have been enabled';
    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while enabling banners.';
    /**
     * @var bool
     */
    protected $isActive = true;
}