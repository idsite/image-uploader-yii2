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

Once the extension is installed, simply use it in your code by  :

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


в .htaccess файл

```
#icache
RewriteCond %{REQUEST_URI} ^/images-cache/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule .* icache.php?uri=%{REQUEST_URI} [L]
```