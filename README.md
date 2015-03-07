Загрузка и сохраниение изображений в Yii 2
==========================================
описание

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist idsite/yii2-images "*"
```

or add

```
"idsite/yii2-images": "*"
```

to the require section of your `composer.json` file.


Usage
-----
Модель изображений должна наследоваться от ImagesModel


```sql
-- таблица для сохранения изображений

-- DROP TABLE images;

CREATE TABLE images
(
  id character varying(32) NOT NULL,
  entity smallint,
  entity_id integer,
  ext character varying(4),
  type smallint,
  body bytea,
  CONSTRAINT pk_images PRIMARY KEY (id)
);

CREATE INDEX idx_images_entity
  ON images
  USING btree
  (entity, entity_id);
```


Конфигурация

```php

'components' => [
        'images' => [
            'class' => 'idsite\images\Component',
            'imagesModelClass' => '\app\models\Images',
            'sizeOptions' => ['50x50']
        ],
]

...

'controllerMap' => [
        'icache' =>'idsite\images\ImageCacheController',
    ],
```


в моделе
```php
 public function behaviors() {
        return [
            'images' => [
                'class' => \idsite\images\UploadImagesBehavior::className(),
                'maxCount' => 30,
                'entity'=>  Images::ENTITY_SEARCH,
        ]];
    }
```


подключаем действие

```php
    public function actions() {
        return [
            'load-images'=>[
                'class'=>  '\idsite\images\ActionLoad',
            ]
          
        ];
    }
```

виджет
```php
 echo \idsite\images\Loader::widget([
                'model'=>$model,
                'attribute'=>'images',
                'url'=>  Url::to(['site/load-images']),
                'size'=>'50x50',
                    ]);
```




в .htaccess файл

```
#icache
RewriteCond %{REQUEST_URI} ^/images-cache/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule .* /icache/index?url=%{REQUEST_URI} [R=302,L]
```