<?php

namespace idsite\images;

use \yii\web\UploadedFile;
use \yii\helpers\FileHelper;

class ActionLoad extends \yii\base\Action {

    /**
     * битовая маска типов файлов из констант GB
     * @var int
     */
    public $imagesTypes = IMG_JPG | IMG_PNG | IMG_GIF;
    public $imagesModelClass;

    public function init() {
        if ($this->imagesModelClass === null) {
            throw new nvalidConfigException('imagesModelClass no set');
        }

        if ($this->imagesTypes === null) {
            throw new nvalidConfigException('imagesTypes no set');
        }
    }

    public function run() {
        $file = UploadedFile::getInstanceByName('file');
        if ($file) {
            $runtime = \Yii::$app->getRuntimePath() . '/imageLoad';
            FileHelper::createDirectory($runtime);
            $fileTemp = $runtime . '/' . uniqid();
            $file->saveAs($fileTemp);

            $imageInfo = getimagesize($fileTemp);
            if ($imageInfo && ($imageInfo[2] & $this->imagesTypes)) {
                $class = $this->imagesModelClass;
                $model = new $class();
                $model['id'] = $this->generateId();
                $ext = $this->getExt($imageInfo[2]);
                if ($ext === null) {
                    $ext = $file->getExtension();
                }
                $model['ext'] = $ext;
                $model['type'] = $imageInfo[2];
                $model['body'] = fopen($fileTemp, 'r');
                if ($model->save()) {
                    return $model['id'];
                } else {
                    throw new \yii\web\HttpException(500, 'изображение не загружено');
                }
                @unlink($fileTemp);
            }

            throw new \yii\web\HttpException(400, 'не правильный тип файла');
        }
        throw new \yii\web\HttpException(400);
    }

    protected function generateId() {
        $arr = ['a', 'b', 'c', 'd', 'e', 'f',
            'g', 'h', 'i', 'j', 'k', 'l',
            'm', 'n', 'o', 'p', 'r', 's',
            't', 'u', 'v', 'x', 'y', 'z',
            '1', '2', '3', '4', '5', '6',
            '7', '8', '9', '0'];
        $str = strtr(\Yii::$app->getSecurity()->generateRandomString(), '_-', $arr[rand(0, count($arr) - 1)] . $arr[rand(0, count($arr) - 1)]);
        return $str;
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
