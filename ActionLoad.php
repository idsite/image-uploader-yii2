<?php

namespace idsite\images;

use \yii\web\UploadedFile;
use \yii\helpers\FileHelper;

class ActionLoad extends \yii\base\Action {

    /**
     * компонент images
     * @var string|Component
     */
    public $images = 'images';


    public function init() {
        $this->images = \yii\di\Instance::ensure($this->images, Component::className());
    }

    public function run() {
        $file = UploadedFile::getInstanceByName('file');
        if ($file) {
            $runtime = \Yii::$app->getRuntimePath() . '/imageLoad';
            FileHelper::createDirectory($runtime,777);
            $fileTemp = $runtime . '/' . uniqid();
            $file->saveAs($fileTemp);
           
            $imageInfo = getimagesize($fileTemp);
          
            if ($imageInfo && ($imageInfo[2] & $this->images->imagesTypes)) {
                $class =  $this->images->imagesModelClass;
                $model = new $class();
                /* @var $model models\ImagesModel */
                $model['id'] = $this->images->generateId();
                $ext = $this->getExt($imageInfo[2]);
                if ($ext === null) {
                    $ext = $file->getExtension();
                }
                $model['ext'] = $ext;
                $model['type'] = $imageInfo[2];
                $model['body'] = fopen($fileTemp, 'r');
                if ($model->save()) {
                    @unlink($fileTemp);
                    return $model['id'];
                } else {
                   @unlink($fileTemp);
                    throw new \yii\web\HttpException(500, 'изображение не загружено '.  implode(',', $model->getFirstErrors()));
                }
            }

            throw new \yii\web\HttpException(400, 'не правильный тип файла');
        }
        throw new \yii\web\HttpException(400);
    }

    

    protected function getExt($type) {
        switch ($type) {
            case IMG_JPG:
                return 'jpg';
            case IMG_PNG:
                return 'png';
            case IMG_GIF:
                return 'gif';
            default:
                return null;
        };
    }

}
