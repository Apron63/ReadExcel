<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'fileName')->fileInput() ?>
    <?= $form->field($model, 'encoding')->textInput() ?>
    <?= $form->field($model, 'separator')->textInput() ?>

    <button class="btn btn-primary">Загрузить</button>

<?php ActiveForm::end() ?>