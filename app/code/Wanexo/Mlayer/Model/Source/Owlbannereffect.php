<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Mlayer\Model\Source;

class Owlbannereffect implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
   public function toOptionArray()

    {
        return array(
            array('value'=>'slide', 'label' => __('Slide')),
			array('value'=>'fade', 'label' => __('Fade')),
			array('value'=>'backSlide', 'label' => __('Back Slide')),
			array('value'=>'goDown', 'label' => __('Go Down')),
			array('value'=>'fadeUp', 'label' => __('Fade Up'))
        );
    }
}
