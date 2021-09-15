<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Mlayer\Model\Source;

class Owlbannereffectin implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
   public function toOptionArray()

    {
        return array(
            array('value'=>'bounceIn', 'label' => __('bounceIn')),
			array('value'=>'bounceInDown', 'label' => __('bounceInDown')),
			array('value'=>'bounceInLeft', 'label' => __('bounceInLeft')),
			array('value'=>'bounceInRight', 'label' => __('bounceInRight')),
			array('value'=>'bounceInUp', 'label' => __('bounceInUp')),
			array('value'=>'fadeIn', 'label' => __('fadeIn')),
			array('value'=>'fadeInDown', 'label' => __('fadeInDown')),
			array('value'=>'fadeInDownBig', 'label' => __('fadeInDownBig')),
			array('value'=>'fadeInLeft', 'label' => __('fadeInLeft')),
			array('value'=>'fadeInLeftBig', 'label' => __('fadeInLeftBig')),
			array('value'=>'fadeInRight', 'label' => __('fadeInRight')),
			array('value'=>'fadeInRightBig', 'label' => __('fadeInRightBig')),
			array('value'=>'fadeInUp', 'label' => __('fadeInUp')),
			array('value'=>'fadeInUpBig', 'label' => __('fadeInUpBig')),
			array('value'=>'flip', 'label' => __('flip')),
			array('value'=>'flipInX', 'label' => __('flipInX')),
			array('value'=>'flipInY', 'label' => __('flipInY')),
			array('value'=>'lightSpeedIn', 'label' => __('lightSpeedIn')),
			array('value'=>'rotateIn', 'label' => __('rotateIn')),
			array('value'=>'rotateInDownLeft', 'label' => __('rotateInDownLeft')),
			array('value'=>'rotateInDownRight', 'label' => __('rotateInDownRight')),
			array('value'=>'rotateInUpLeft', 'label' => __('rotateInUpLeft')),
			array('value'=>'rotateInUpRight', 'label' => __('rotateInUpRight')),
			array('value'=>'slideInUp', 'label' => __('slideInUp')),
			array('value'=>'slideInDown', 'label' => __('slideInDown')),
			array('value'=>'slideInLeft', 'label' => __('slideInLeft')),
			array('value'=>'slideInRight', 'label' => __('slideInRight')),
			array('value'=>'zoomIn', 'label' => __('zoomIn')),
			array('value'=>'zoomInDown', 'label' => __('zoomInDown')),
			array('value'=>'zoomInLeft', 'label' => __('zoomInLeft')),
			array('value'=>'zoomInRight', 'label' => __('zoomInRight')),
			array('value'=>'zoomInUp', 'label' => __('zoomInUp')),
			array('value'=>'rollIn', 'label' => __('rollIn')),
        );
    }
}
