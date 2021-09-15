<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Plugin;

class TabLabel
{
    const CUSTOMERS_FIELDSET_NAMESPACE = 'customer';

    const GUESTS_FIELDSET_NAMESPACE = 'guest';

    /**
     * @var \Amasty\Segments\Helper\Customer\Data
     */
    protected $customerHelper;

    /**
     * TabLabel constructor.
     * @param \Amasty\Segments\Helper\Customer\Data $customerHelper
     */
    public function __construct(
        \Amasty\Segments\Helper\Customer\Data $customerHelper
    ) {
        $this->customerHelper = $customerHelper;
    }

    /**
     * @param \Magento\Ui\Component\Form\Fieldset $subject
     * @param $result
     * @return \Magento\Framework\Phrase
     */
    public function afterGetComponentName(\Magento\Ui\Component\Form\Fieldset $subject, $result)
    {

        $subjectName = $subject->getName();
        $fieldsetsArray = [
                self::CUSTOMERS_FIELDSET_NAMESPACE,
                $subjectName == self::GUESTS_FIELDSET_NAMESPACE
            ];


        if (in_array($subjectName, $fieldsetsArray)) {
            $collection = $this->customerHelper->{'getFiltered' . ucfirst($subjectName) . 'Collection'}();
            $configs = $subject->getConfig();

            if (!array_key_exists('is_modified', $configs)) {
                $configs['label'] = __(sprintf($configs['label'] . ' (%s)', $collection->getSize()));
                $configs['is_modified'] = 1;
                $subject->setConfig($configs);
            }
        }

        return $result;
    }
}
