<?php
namespace Wanexo\Brand\Model;

use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Model\Exception as FrameworkException;
use Magento\Framework\File\Uploader;
use Wanexo\Brand\Model\Brand\Image as ImageModel;

class Upload
{
    /**
     * uploader factory
     *
     * @var \Magento\Core\Model\File\UploaderFactory
     */
    protected $uploaderFactory;
    
    protected $imageModel;

    /**
     * constructor
     *
     * @param UploaderFactory $uploaderFactory
     */
    public function __construct(UploaderFactory $uploaderFactory, ImageModel $imageModel)
    {
        $this->uploaderFactory = $uploaderFactory;
        $this->imageModel = $imageModel;
    }
   
    public function uploadFileAndGetName($input, $destinationFolder, $data)
    {
        try {
            if (isset($data[$input]['delete'])) {
                if($input == "brand_image")
                {
                    $imgname =  $data[$input]['value'];
                    $path = $this->imageModel->getBaseDir();
                    $imgpath = $path.$imgname;
                    if(file_exists($imgpath)){
                    unlink($imgpath);}
                }
                elseif($input == "brand_thumbimage")
                {  
                     $imgname =  $data[$input]['value'];
                     $value = explode('.',$imgname);
                     $thumbimage = $value[0].'_thumb.'.$value[1];
                     $path = $this->imageModel->getBaseDir();
                     $thumb = $path."thumb/";
                     $thumbpath = $thumb.$thumbimage;
                     if(file_exists($thumbpath)){
                     unlink($thumbpath);}
                }
                elseif( file_exists($imgpath) && file_exists($thumbpath) )
                {
                  $imgname =  $data["brand_image"]['value'];
                  $pathdir = $this->imageModel->getBaseDir();
                  $fullpath = $pathdir.$imgname;  
                     unlink($fullpath);
                $thumbname =  $data["brand_thumbimage"]['value'];
                $value = explode('.',$thumbname);
                $thumbimg = $value[0].'_thumb.'.$value[1];
                $thumbdir = $this->imageModel->getBaseDir();
                $thumbfile = $thumbdir."thumb/";
                $fullpaththumb = $thumbfile.$thumbimg;
                     unlink($fullpaththumb);
                }
                return '';
            } else {

                $uploader = $this->uploaderFactory->create(['fileId' => $input]);
                $uploader->setAllowRenameFiles(true);
               // $uploader->setFilesDispersion(true);
                //$uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destinationFolder);
                return $result['file'];
            }
        } catch (\Exception $e) {
            if ($e->getCode() != Uploader::TMP_NAME_EMPTY) {
                throw new FrameworkException($e->getMessage());
            } else {
                if (isset($data[$input]['value'])) {
                    return $data[$input]['value'];
                }
            }
        }
        return '';
    }
}
