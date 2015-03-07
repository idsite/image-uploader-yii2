<?php
/* @var $this yii\web\View */
/* @var $images Array */
/* @var $name string */
/* @var $label string */
/* @var $size string */
/* @var $id string */

use yii\helpers\Html;
?>

<div class="images-loader" id="<?= $id ?>">
    <?php
    if ($label) {
        echo Html::label($label);
    }
    ?>
    <div class="images-items">

    </div>
    <div class="images-inputs">

    </div>

    <input type="file" name="" class="images-input-file" multiple >
</div>