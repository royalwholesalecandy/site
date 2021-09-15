<?php
namespace Wanexo\Brand\Controller\Adminhtml\Brandaction;

class MassEnable extends MassDisable
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 brands have been enabled';
    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while enabling brands.';
    /**
     * @var bool
     */
    protected $status = true;
}
