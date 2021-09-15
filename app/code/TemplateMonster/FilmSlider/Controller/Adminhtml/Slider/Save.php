<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action;
use Magento\Framework\Filesystem;
use TemplateMonster\FilmSlider\Api\SliderRepositoryInterface;
use TemplateMonster\FilmSlider\Api\Data\SliderInterface;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var SliderRepositoryInterface
     */
    protected $_sliderRepository;

    /**
     * @var FileSystem
     */
    protected $_filesystem;

    protected $_dataProcessor;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        SliderRepositoryInterface $sliderRepository,
        Filesystem $filesystem,
        PostDataProcessor $dataProcessor
    ) {
        $this->_sliderRepository = $sliderRepository;
        $this->_filesystem = $filesystem;
        $this->_dataProcessor = $dataProcessor;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_FilmSlider::filmslider_save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('slider_id');
            if ($id) {
                $model = $this->_sliderRepository->getById($id);
            } else {
                $model = $this->_sliderRepository->getModelInstance();
            }

            $model->addData($data);

            $args = [SliderInterface::NAME,
                SliderInterface::STORE,
                SliderInterface::STATUS,
                SliderInterface::PARAMS,
                'form_key'];

            $dataParams = $this->getRequest()->getPostValue();
            foreach ($args as $arg) {
                if (array_key_exists($arg, $dataParams)) {
                    unset($dataParams[$arg]);
                }
            }


            $sliderScheme = json_decode($dataParams['sliderScheme'], true);
            if($sliderScheme['slideImage']) {
                $this->cleanMediaDir();
                foreach ($sliderScheme['slideImage'] as $slide) {
                    if($slide) {

//                        preg_match_all("/data:image(.*);base64/",$slide['src'], $matches);
//                        $imgExt = '.'.substr($matches[1][0],1);

                        //$tempExplode == ['data:image', '$ext;...']
                        $tempExplode = explode('/', substr($slide['src'], 0, 20), 2);
                        if(isset($tempExplode[1])) {
                            $extractExt = explode(';', $tempExplode[1]);
                            $imgExt = '.'.$extractExt[0];
                            $slideIds = array_keys($sliderScheme['slideImage'], $slide);
                            foreach ($slideIds as $slideId) {
                                $this->base64ToImage( $slide['src'], 'slide-'.$slideId.$imgExt );
    //                          $sliderScheme['slideImage'][$slideId] = null;
                                $sliderScheme['slideImage'][$slideId]['ext'] = $imgExt;
                            }
                        }
                    }
                }
            }

            $dataParams['sliderScheme'] = json_encode($sliderScheme);

            if ($dataParams) {
                $model->setParams($dataParams);
            }


            $this->_eventManager->dispatch(
                'film_slider_prepare_save',
                ['slider' => $model, 'request' => $this->getRequest()]
            );

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved this slider.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['slider_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/index');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the slider.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['slider_id' => $this->getRequest()->getParam('slider_id')]);
        }
        return $resultRedirect->setPath('*/*/index');
    }

    function getMediaPath(){
        return  $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('slider/');
    }

    function base64ToImage( $base64_string, $output_file ) {
        $path = $this->getMediaPath();
        if(!file_exists($path)) {
            mkdir($path, 0777);
        }
        $file = fopen( $path.$output_file, "wb" );
        $data = explode( ',', $base64_string );
        fwrite( $file, base64_decode($data[1]) );
        fclose( $file );
        return $output_file;
    }

    function cleanMediaDir() {
        $path = $this->getMediaPath();
        $files = glob($path."/*");
        if (count($files) > 0) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

}
