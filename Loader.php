<?php

namespace idsite\images;

class Loader extends \yii\widgets\InputWidget {

    /**
     * @var \yii\db\ActiveRecord
     */
    public $model;
    public $size;
    public $url;
    public $viewFile = 'loader';
    protected $behaviorModel;

    public function init() {
        parent::init();

        if ($this->model) {
            foreach ($this->model->getBehaviors() as $behavior) {
                if ($behavior instanceof UploadImagesBehavior) {
                    $this->behaviorModel = $behavior;
                    break;
                }
            }
            if ($this->behaviorModel === null) {
                throw new \yii\base\InvalidConfigException('The model does not set behavior');
            }
        }

        LoaderAsset::register($this->getView());
    }

    public function run() {

        $images = [];
        $label = null;
        $maxCount = null;

        if ($this->model) {
            if ($this->model[$this->attribute] && is_array($this->model[$this->attribute])) {
                $images = $this->model[$this->attribute];
            } elseif (!$this->model->isNewRecord) {
                $images = \yii\helpers\ArrayHelper::getColumn($this->model->getImagesQuery()->select('id')->asArray()->all(), 'id');
            }
            $name = \yii\helpers\Html::getInputName($this->model, $this->attribute);
            $label = $this->model->getAttributeLabel($this->attribute);
            $maxCount = $this->behaviorModel->maxCount;
        } else {
            $name = $this->name;
        }
        $id = $this->getId();


        $this->view->registerJs("$('#$id').imagesLoader(" . \yii\helpers\Json::encode([
                    'url' => $this->url,
                    'images' => $images,
                    'maxCount' => $maxCount,
                    'name'=>$name,
                    'fnGetUrlImages' => new \yii\web\JsExpression("function (id) {  return id==='loading'?'':'<img src=\"/images-cache/' + id+'_{$this->size}\">'  }"),
                ]) . ")");

        echo $this->render($this->viewFile, ['name' => $name, 'images' => $images, 'label' => $label, 'size' => $this->size, 'id' => $id]);
    }

}
