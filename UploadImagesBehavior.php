<?php

namespace idsite\images;

use InvalidConfigException;
use yii\db\ActiveRecord;

class UploadImagesBehavior extends \yii\base\Behavior {

    /**
     * обозначает модель
     * @var int
     */
    public $entity;
    public $maxCount;
    public $required = false;
    public $imagesModelClass;
    private $_imagesAdd;
    private $_imagesDelete;

    /**
     * компонент images
     * @var string|Component
     */
    public $images = 'images';

    /*
     * атрибут в моделе для передачи масива айдишников изображений
     */
    public $attribute = 'images';

    public function init() {

        $this->images = \yii\di\Instance::ensure($this->images, Component::className());

        if ($this->entity === null) {
            throw new InvalidConfigException('entity no set');
        }

        if ($this->attribute === null) {
            throw new nvalidConfigException('attribute no set');
        }
    }

    public function events() {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
        ];
    }

    public function afterSave($event) {
        if ($this->_imagesAdd) {
            foreach ($this->_imagesAdd as $model) {
                $model->delete();
            }
        }

        if ($this->_imagesAdd) {
            foreach ($this->_imagesAdd as $model) {
                $model['entity'] = $this->entity;
                $model['entity_id'] = $this->owner->primaryKey;
                if (!$model->save()) {
                    throw new \yii\base\Exception('photo not save');
                }
            }
        }
    }

    public function beforeValidate($event) {
        $class = $this->images->imagesModelClass;
        $this->_imagesAdd = [];
        $this->_imagesDelete = [];


        if (!empty($this->owner[$this->attribute])) {
            $ids = (array) $this->owner[$this->attribute];
            foreach ($ids as $id) {
                if (strncmp('delete/', $id, 7) === 0) {
                    if (!$this->owner->isNewRecord) {
                        $id = substr($id, 7);
                        if ($modelImg = $class::find()->entity($this->entity, $this->owner->primaryKey)->where(['id' => $id])->selectWithoutBody()->one()) {
                            $this->_imagesDelete[] = $modelImg;
                        }
                    }
                } elseif (strncmp('add/', $id, 4) === 0) {
                    $id = substr($id, 4);
                    if ($modelImg = $class::find()->withoutEntity()->where(['id' => $id])->selectWithoutBody()->one()) {
                        $this->_imagesAdd[] = $modelImg;
                    }
                }
            }
        }

        if ($this->maxCount !== null || $this->required) {
            $bcount = $this->getCountImage() + count($this->_imagesAdd) - count($this->_imagesDelete);

            if ($this->maxCount !== null) {
                if ($bcount > $this->maxCount) {
                    $this->owner->addError($this->attribute, 'маскимально изображений ' . $this->maxCount);
                }
            }

            if ($this->required && $bcount === 0) {
                $this->owner->addError($this->attribute, 'загрузите изображение');
            }
        }
    }

    /**
     * 
     * @return \yii\db\ActiveQuery;
     */
    public function getImagesQuery() {
        $class = $this->images->imagesModelClass;
        return $class::find()->entity($this->entity, $this->owner->primaryKey)->selectWithoutBody();
    }

    /**
     * 
     * @return \yii\db\ActiveQuery;
     */
    public function getImagesRel() {
        return $this->owner->hasMany($this->images->imagesModelClass, ['entity_id' => 'id'])->where(['entity' => $this->entity])->select(['id', 'entity', 'entity_id', 'ext', 'type']);
    }

    public function getCountImage() {
        if ($this->owner->isNewRecord) {
            return 0;
        } else {
            return $this->getImagesQuery()->count();
        }
    }

}
