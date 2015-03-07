<?php

namespace idsite\images\models;

use Yii;

class ImagesQuery extends \yii\db\ActiveQuery {

    /**
     * выбор картинок по сущности 
     * @return self
     */
    public function entity($entity, $entityId) {
        $this->andWhere(['entity' => $entity, 'entity_id' => $entityId]);
        return $this;
    }

    /**
     * поиск времянных картинок
     * @return self
     */
    public function withoutEntity() {
        $this->andWhere('entity IS NULL and entity_id IS NULL');
        return $this;
    }

    /**
     *  выбрать без содержимого картинки
     * @return self
     */
    public function selectWithoutBody() {
        $this->select(['id', 'entity', 'entity_id', 'ext', 'type']);
        return $this;
    }

}