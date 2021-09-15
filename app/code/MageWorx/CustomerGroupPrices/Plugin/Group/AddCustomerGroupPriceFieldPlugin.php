<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Plugin\Group;

use MageWorx\CustomerGroupPrices\Model\Config\Source\GroupCustomers;
use \Magento\Framework\App\Request\Http;
use MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices;
use \Magento\Framework\Data\Form;

class AddCustomerGroupPriceFieldPlugin
{
    /**
     * Request object
     *
     * @var Http
     */
    protected $request;

    /**
     * @var CustomerGroupPrices
     */
    protected $customerGroupPricesResourceModel;

    /**
     * @var GroupCustomers
     */
    protected $configGroupCustomers;

    /**
     * AddCustomerGroupPriceFieldPlugin constructor.
     *
     * @param CustomerGroupPrices $customerGroupPricesResourceModel
     * @param GroupCustomers $configGroupCustomers
     * @param Http $request
     */
    public function __construct(
        CustomerGroupPrices $customerGroupPricesResourceModel,
        GroupCustomers $configGroupCustomers,
        Http $request
    ) {
        $this->customerGroupPricesResourceModel = $customerGroupPricesResourceModel;
        $this->configGroupCustomers             = $configGroupCustomers;
        $this->request                          = $request;
    }

    /**
     * @param \Magento\Customer\Block\Adminhtml\Group\Edit\Form $subject
     * @param Form $form
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSetForm(\Magento\Customer\Block\Adminhtml\Group\Edit\Form $subject, Form $form)
    {
        $allRequest = $this->request->getParams();
        $groupId    = null;

        if (array_key_exists('id', $allRequest)) {
            $fieldset = $form->getElement('base_fieldset');
            $fieldset->addField(
                'mageworx_group_price',
                'text',
                [
                    'name'     => 'mageworx_group_price',
                    'label'    => __("Group Price"),
                    'title'    => __('Group Price'),
                    'class'    => '',
                    'required' => false,
                    'values'   => '',
                ]
            );

            $fieldset->addField(
                'mageworx_group_price_type',
                'select',
                [
                    'name'     => 'mageworx_group_price_type',
                    'label'    => __('Price Type'),
                    'title'    => __('Price Type'),
                    'class'    => '',
                    'required' => false,
                    'values'   => $this->configGroupCustomers->toOptionArray(),
                ]
            );

            $groupId = $allRequest['id'];
            if ($groupId !== null) {
                $groupPrice = $this->customerGroupPricesResourceModel->getGroupPrice($groupId);

                if (!empty($groupPrice)) {
                    $form->addValues(
                        [
                            'mageworx_group_price'      => $groupPrice['price'],
                            'mageworx_group_price_type' => $groupPrice['price_type']
                        ]
                    );
                }
            }
        }
    }
}