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
    /*
     * атрибут в моделе для передачи масива айдишников изображений
     */
    public $attribute = 'images';

    public function init() {

        if ($this->entity === null) {
            throw new InvalidConfigException('entity no set');
        }

        if ($this->imagesModelClass === null) {
            throw new nvalidConfigException('imagesModelClass no set');
        }

        if ($this->attribute === null) {
            throw new nvalidConfigException('attribute no set');
        }
    }

    public function events() {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate($event) {

        $class = $this->imagesModelClass;
        $imagesAdd = [];
        $imagesDelete = [];
        if (!empty($this->owner[$this->attribute])) {
            $ids = (array) $this->owner[$this->attribute];
            foreach ($ids as $id) {
                if (strncmp('delete/', $id, 7) === 0) {
                    if (!$this->owner->isNewRecord) {
                        $id = substr($id, 7);
                        if ($modelImg = $class::find()->where(['id' => $id, 'entity' => $this->entity, 'entity_id' => $this->owner->primaryKey])->select(['id'])->one()) {
                            $imagesDelete[] = $modelImg;
                        }
                    }
                } elseif (strncmp('add/', $id, 4) === 0) {
                    $id = substr($id, 4);
                    if ($modelImg = $class::find()->where(['id' => $id, 'entity' => null, 'entity_id' => null])->select(['id'])->one()) {
                        $imagesAdd[] = $modelImg;
                    }
                }
            }
        }
        
        if ($this->maxCount !== null || $this->required) {
            $bcount = $this->getCountImage() + count($imagesAdd) - count($imagesDelete);

            if ($this->maxCount !== null) {
                if ($bcount > $this->maxCount) {
                    $this->owner->addError($this->attribute, 'маскимально изображений ' . $this->maxCount);
                }
            }

            if ($this->required && $bcount === 0) {
                $this->owner->addError($this->attribute, 'загрузите изображение');
            }
        }

        //нужно передать в приватные свойства и после сохранения обработать
    }

    /**
     * 
     * @return \yii\db\ActiveQuery;
     */
    public function getImagesQuery() {
        $class = $this->imagesModelClass;
        return $class::find()->where(['entity' => $this->entity, 'entity_id' => $this->owner->primaryKey]);
    }

    public function getCountImage() {
        if ($this->owner->isNewRecord) {
            return 0;
        } else {
            return $this->getImagesQuery()->count();
        }
    }

}
