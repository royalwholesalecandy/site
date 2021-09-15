<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Mlayer\Model\Source;

class Bannereffect implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
   public function toOptionArray()

    {

        return array(

            array(

                  'value'     => 'random',
                  'label'     => __('random'),

              ),



              array(

                  'value'     => 'simpleFade',

                  'label'     => __('simpleFade'),

              ),

			  array(

                  'value'     => 'curtainTopLeft',

                  'label'     => __('curtainTopLeft'),

              ),

			   array(

                  'value'     => 'curtainTopRight',

                  'label'     => __('curtainTopRight'),

              ),

			   array(

                  'value'     => 'curtainBottomLeft',

                  'label'     => __('curtainBottomLeft'),

              ),

			   array(

                  'value'     => 'curtainBottomRight',

                  'label'     => __('curtainBottomRight'),

              ),

			   array(

                  'value'     => 'curtainSliceLeft',

                  'label'     => __('curtainSliceLeft'),

              ),

			   array(

                  'value'     => 'curtainSliceRight',

                  'label'     => __('curtainSliceRight'),

              ),

			   array(

                  'value'     => 'blindCurtainTopLeft',

                  'label'     => __('blindCurtainTopLeft'),

              ),

			   array(

                  'value'     => 'blindCurtainTopRight',

                  'label'     => __('blindCurtainTopRight'),

              ),

			   array(

                  'value'     => 'blindCurtainBottomLeft',

                  'label'     => __('blindCurtainBottomLeft'),

              ),

			   array(

                  'value'     => 'blindCurtainBottomRight',

                  'label'     => __('blindCurtainBottomRight'),

              ),

			   array(

                  'value'     => 'blindCurtainSliceBottom',

                  'label'     => __('blindCurtainSliceBottom'),

              ),

			   array(

                  'value'     => 'blindCurtainSliceTop',

                  'label'     => __('blindCurtainSliceTop'),

              ),

			   array(

                  'value'     => 'stampede',

                  'label'     => __('stampede'),

              ),

			   array(

                  'value'     => 'mosaic',

                  'label'     => __('mosaic'),

              ),

			   array(

                  'value'     => 'mosaicReverse',

                  'label'     => __('mosaicReverse'),

              ),

			   array(

                  'value'     => 'mosaicRandom',

                  'label'     => __('mosaicRandom'),

              ),

			   array(

                  'value'     => 'mosaicSpiral',

                  'label'     => __('mosaicSpiral'),

              ),

			   array(

                  'value'     => 'mosaicSpiralReverse',

                  'label'     => __('mosaicSpiralReverse'),

              ),

			   array(

                  'value'     => 'topLeftBottomRight',

                  'label'     => __('topLeftBottomRight'),

              ),

			   array(

                  'value'     => 'topLeftBottomRight',

                  'label'     => __('topLeftBottomRight'),

              ),

			   array(

                  'value'     => 'bottomRightTopLeft',

                  'label'     => __('bottomRightTopLeft'),

              ),

			   array(

                  'value'     => 'bottomLeftTopRight',

                  'label'     => __('bottomLeftTopRight'),

              ),

			   array(

                  'value'     => 'bottomLeftTopRight',

                  'label'     => __('bottomLeftTopRight'),

              ),

        );
    }
}
