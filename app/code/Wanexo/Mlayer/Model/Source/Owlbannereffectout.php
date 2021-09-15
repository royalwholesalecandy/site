<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Mlayer\Model\Source;

class Owlbannereffectout implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
   public function toOptionArray()

    {
        return array(
            array('value'=>'bounceOut', 'label' => __('bounceOut')),
			array('value'=>'bounceOutDown', 'label' => __('bounceOutDown')),
			array('value'=>'bounceOutLeft', 'label' => __('bounceOutLeft')),
			array('value'=>'bounceOutRight', 'label' => __('bounceOutRight')),
			array('value'=>'bounceOutUp', 'label' => __('bounceOutUp')),
			array('value'=>'fadeOut', 'label' => __('fadeOut')),
			array('value'=>'fadeOutDown', 'label' => __('fadeOutDown')),
			array('value'=>'fadeOutDownBig', 'label' => __('fadeOutDownBig')),
			array('value'=>'fadeOutLeft', 'label' => __('fadeOutLeft')),
			array('value'=>'fadeOutLeftBig', 'label' => __('fadeOutLeftBig')),
			array('value'=>'fadeOutRight', 'label' => __('fadeOutRight')),
			array('value'=>'fadeOutRightBig', 'label' => __('fadeOutRightBig')),
			array('value'=>'fadeOutUp', 'label' => __('fadeOutUp')),
			array('value'=>'fadeOutUpBig', 'label' => __('fadeOutUpBig')),
			array('value'=>'flipOutX', 'label' => __('flipOutX')),
			array('value'=>'flipOutY', 'label' => __('flipOutY')),
			array('value'=>'lightSpeedOut', 'label' => __('lightSpeedOut')),
			array('value'=>'rotateOut', 'label' => __('rotateOut')),
			array('value'=>'rotateOutDownLeft', 'label' => __('rotateOutDownLeft')),
			array('value'=>'rotateOutDownRight', 'label' => __('rotateOutDownRight')),
			array('value'=>'rotateOutUpLeft', 'label' => __('rotateOutUpLeft')),
			array('value'=>'rotateOutUpRight', 'label' => __('rotateOutUpRight')),
			array('value'=>'slideOutUp', 'label' => __('slideOutUp')),
			array('value'=>'slideOutDown', 'label' => __('slideOutDown')),
			array('value'=>'slideOutLeft', 'label' => __('slideOutLeft')),
			array('value'=>'slideOutRight', 'label' => __('slideOutRight')),
			array('value'=>'zoomOut', 'label' => __('zoomOut')),
			array('value'=>'zoomOutDown', 'label' => __('zoomOutDown')),
			array('value'=>'zoomOutLeft', 'label' => __('zoomOutLeft')),
			array('value'=>'zoomOutRight', 'label' => __('zoomOutRight')),
			array('value'=>'zoomOutUp', 'label' => __('zoomOutUp')),
			array('value'=>'rollOut', 'label' => __('rollOut')),
        );
    }
}
