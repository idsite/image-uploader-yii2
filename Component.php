<?php

namespace idsite\images;

use Imagine\Image\ImageInterface;

class Component extends \yii\base\Object {

    /**
     * путь для сохранения файлов
     * @var string
     */
    public $imagesCacheAlias = '@webroot/images-cache';

    /**
     * URL к папке с изображениями
     * @var string
     */
    public $imagesCacheUrl = '/images-cache';

    /**
     * Класс модели картинок
     * @var string
     */
    public $imagesModelClass;

    /**
     * разрешонные варианты размеров. ввиде ШxВ
     * @var array
     */
    public $sizeOptions = [];

    /**
     * количество вложенных подпапок, при сохранении картинки
     * @var type 
     */
    public $nesting = 2;
    public $thumbnailMode = ImageInterface::THUMBNAIL_OUTBOUND;

    /**
     * битовая маска типов файлов из констант GB
     * @var int
     */
    public $imagesTypes;
    private $_imagesPath;

    public function init() {
        
        if ($this->imagesTypes === null) {
            $this->imagesTypes = IMG_JPG | IMG_PNG | IMG_GIF;
        }

        if ($this->imagesCacheAlias === null) {
            throw new \yii\base\InvalidConfigException('imageCacheAlias is NULL');
        }

        if ($this->imagesModelClass === null) {
            throw new \yii\base\InvalidConfigException('imagesModelClass is NULL');
        }
    }

    public function getImagesCachePath() {
        if ($this->_imagesPath === null) {
            $this->_imagesPath = \Yii::getAlias($this->imagesCacheAlias);
        }
        return $this->_imagesPath;
    }

    /**
     * возврашяет айдишкик с добавленными папкими
     * @param string $id  'qw/er/qwer'
     * @return string
     */
    public function getIdWithFolders($id) {
        if ($this->nesting) {
            $f = '';
            for ($i = 0; $i < $this->nesting; $i++) {
                $f.= substr($id, $i * 2, 2) . '/';
            }
            $id = $f . $id;
        }
        return $id;
    }

    /**
     * герерация ид фотки. не должно содержать _
     * @return string
     */
    public function generateId() {
        $arr = ['a', 'b', 'c', 'd', 'e', 'f',
            'g', 'h', 'i', 'j', 'k', 'l',
            'm', 'n', 'o', 'p', 'r', 's',
            't', 'u', 'v', 'x', 'y', 'z',
            '1', '2', '3', '4', '5', '6',
            '7', '8', '9', '0'];
        $str = strtolower(strtr(\Yii::$app->getSecurity()->generateRandomString(), '_-', $arr[rand(0, count($arr) - 1)] . $arr[rand(0, count($arr) - 1)]));
        return $str;
    }

}
