<?php

use yii\helpers\Html;

/* @var $columnsList */
/* @var $inputName */

echo Html::dropDownList($inputName, '', $columnsList, [
    'class' => 'form-control column-selector',
    'prompt' => '-----',
]);