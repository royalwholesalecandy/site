<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Block\Adminhtml\Segment\Edit\Tab;

use Amasty\Segments\Helper\Base;
use Magento\Backend\Block\Widget\Form\Generic;

class Conditions extends Generic implements \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    /**
     * @var Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $_conditions;

    /**
     * @var Base
     */
    protected $helper;

    /**
     * @var \Amasty\Segments\Model\SegmentFactory
     */
    protected $segmentFactory;

    /**
     * @var \Amasty\Segments\Model\SegmentRepository
     */
    protected $segmentRepository;

    /**
     * Conditions constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param Base $helper
     * @param \Amasty\Segments\Model\SegmentFactory $segmentFactory
     * @param \Amasty\Segments\Model\SegmentRepository $segmentRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Amasty\Segments\Helper\Base $helper,
        \Amasty\Segments\Model\SegmentFactory $segmentFactory,
        \Amasty\Segments\Model\SegmentRepository $segmentRepository,
        array $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions = $conditions;
        $this->helper = $helper;
        $this->segmentFactory = $segmentFactory;
        $this->segmentRepository = $segmentRepository;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        $formName = 'amastysegments_segment_form';
        $model = $this->segmentRepository->getSegmentFromRegistry()
            ? $this->segmentRepository->getSegmentFromRegistry() : $this->segmentFactory->create();

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('segment_');

        $renderer = $this->_rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('amastysegments/segment/newConditionHtml/form/segment_conditions_fieldset',
                ['form_namespace' => $formName]
            )
        );

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            ['legend' => __('Apply the rule only if the following conditions are met (leave blank for all products)')]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name' => 'conditions',
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'required' => true,
                'data-form-part' => $formName
            ]
        )->setRule(
            $model ? $model->getSalesRule() : null
        )->setRenderer(
            $this->_conditions
        );

        $fieldset->addField(
            'guest_note',
            'note',
            [
                'name' => 'guest_note',
                'text' => __('<b>*</b> available for guests and registered customers')
            ]
        );

        $form->setValues($model->getData());
        $this->setConditionFormName($model->getSalesRule()->getConditions(), $formName);
        $this->setForm($form);
        
        return parent::_prepareForm();
    }

    /**
     * Handles addition of form name to condition and its conditions.
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $conditions
     * @param string $formName
     * @return void
     */
    private function setConditionFormName(\Magento\Rule\Model\Condition\AbstractCondition $conditions, $formName)
    {
        $conditions->setFormName($formName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }
}
