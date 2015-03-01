<?php

namespace idsite\images;

/**
 * Description of ImageCacheController
 *
 * @author Derzhavin A. derzhavin@lightsoft.ru
 */
class ImageCacheController extends \yii\web\Controller {

    public $imageCachePath = '@webroot/images-cache';
    public $imagesModelClass;
    public $sizeOptions = [];

    public function init() {

        if ($this->imageCachePath === null) {
            throw new \yii\base\InvalidConfigException('imageCachePath is NULL');
        }
        if ($this->imagesModelClass === null) {
            throw new \yii\base\InvalidConfigException('imagesModelClass is NULL');
        }
    }

    public function actionIndex($url) {
        $imageCachePath = \Yii::getAlias($this->imageCachePath);
        $baseUrl = \Yii::$app->getRequest()->getBaseUrl() . '/' . dirname($imageCachePath) . '/';
        $urlRedirect = null;

        $name = basename($url);
        if (file_exists($imageCachePath . '/' . $name)) {
            $urlRedirect = $baseUrl . $name;
        } else {
            $class = $this->imagesModelClass;
            $path_info = pathinfo($name);
            list($fileName, $size) = explode('_', $path_info['filename']);

            if ($size && !in_array($size, $this->sizeOptions)) {
                throw new \yii\web\HttpException(400, 'Available sizes:' . implode(',', $this->sizeOptions));
            }

            $model = $class::find()->where('id=:id and entity is not NULL and entity_id is NOT NULL', [':id' => $fileName])->select(['id', 'ext', 'type'])->one();

            if ($model === null) {
                throw new \yii\web\HttpException(404);
            }

            $name = $model['id'];
            if ($size) {
                $name.='_' . $size;
            }
            $name.='.' . $model['ext'];

            if (!file_exists($imageCachePath . '/' . $name)) {
                
                
                
                
            } else {
                $urlRedirect = $baseUrl . $name;
            }



            $ext = $path_info['extension'];
        }
    }

}
