<?php

namespace idsite\images\models;

/**
 * 
 *
 * @property string $id
 * @property integer $entity
 * @property integer $entity_id
 * @property string $ext
 * @property integer $type
 * @property resource $body
 */
abstract class ImagesModel extends \yii\db\ActiveRecord {

    /**
     * компонент images
     * @var string|Component
     */
    public $images = 'images';

    public function init() {
        $this->images = \yii\di\Instance::ensure($this->images, \idsite\images\Component::className());
    }

    /**
     * 
     * @return \idsite\images\models\ImagesQuery
     */
    public static function find() {
        return new ImagesQuery(get_called_class());
    }

    public function getUrl($size = null) {
        return $this->images->imagesCacheUrl . '/' . $this->images->getIdWithFolders($this->id) . ($size ? '_' . $size : '') . '.' . $this->ext;
    }

    public function rules() {
        return [
            [['id'], 'required'],
            [['entity', 'entity_id', 'type'], 'integer'],
            [['body'], 'safe'],
            [['id'], 'string', 'max' => 32],
            [['ext'], 'string', 'max' => 4]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'entity' => 'Entity',
            'entity_id' => 'Entity ID',
            'ext' => 'Ext',
            'type' => 'Type',
            'body' => 'Body',
        ];
    }

}
