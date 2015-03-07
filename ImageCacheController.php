<?php

namespace idsite\images;

use yii\imagine\Image;

/**
 * Description of ImageCacheController
 *
 * @author Derzhavin A. derzhavin@lightsoft.ru
 */
class ImageCacheController extends \yii\web\Controller {

    /**
     * компонент images
     * @var string|Component
     */
    public $images = 'images';

    public function init() {
        $this->images = \yii\di\Instance::ensure($this->images, Component::className());
    }

    public function actionIndex($url) {
        $imageCachePath = $this->images->getImagesCachePath();
        $baseUrl = $this->images->imagesCacheUrl;
        $urlRedirect = null;
        $pathFile = $this->images->getIdWithFolders(basename($url));

        if (file_exists($imageCachePath . '/' . $pathFile)) {
            $urlRedirect = $baseUrl . '/' . $pathFile;
        } else {
            $class = $this->images->imagesModelClass;
            $path_info = pathinfo($pathFile);
            @list($fileName, $size) = explode('_', $path_info['filename']);
       
            if ($size && !in_array($size, $this->images->sizeOptions)) {
                throw new \yii\web\HttpException(400, 'Available sizes:' . implode(',', $this->images->sizeOptions));
            }

            $model = $class::find()->where(['id' => $fileName])->selectWithoutBody()->one();

            if ($model === null) {
                throw new \yii\web\HttpException(404);
            }

            $imageCachePath = $imageCachePath . '/' . $path_info['dirname'];

            \yii\helpers\FileHelper::createDirectory($imageCachePath);

            $nameOrigin = $model['id'] . '.' . $model['ext'];


            if (!file_exists($imageCachePath . '/' . $nameOrigin)) {
                $body = $class::find()->select('body')->where(['id' => $model['id']])->asArray()->one();
                file_put_contents($imageCachePath . '/' . $nameOrigin, $body['body']);
            }

            if ($size) {
                $nameSize = $model['id'] . '_' . $size . '.' . $model['ext'];
                list($w, $h) = explode('x', $size);

                Image::thumbnail($imageCachePath . '/' . $nameOrigin, $w, $h, $this->images->thumbnailMode)
                        ->save($imageCachePath . '/' . $nameSize, ['quality' => 80]);
                $urlRedirect = $baseUrl . '/' . $path_info['dirname'] . '/' . $nameSize;
            } else {
                $urlRedirect = $baseUrl . '/' . $path_info['dirname'] . '/' . $nameOrigin;
            }
        }

        return $this->redirect($urlRedirect, 302);
    }

}
