<?php

namespace idsite\images;

class LoaderAsset extends \yii\web\AssetBundle {

    public $css = [
        'imagesLoader.css',
    ];
    public $js = [
        'imagesLoader.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];

    public function init() {

        $this->sourcePath = __DIR__ . '/assets';
        parent::init();
    }

}
