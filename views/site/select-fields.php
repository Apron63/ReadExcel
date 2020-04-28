<?php

use app\widgets\ColumnFilterWidget;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */
/* @var $fileName */
/* @var $columnsName */
/* @var $dataProvider */
/* @var $filterModel */
/* @var $encoding */
/* @var $separator */

$this->title = 'Подготовка к импорту';

/*
 * Форма выбора колонок.
 * Индекс в данных от Excel начинается с 1.
 */
?>

<div class="row">
    <table class="table table-striped table-bordered">
        <thead>
            <tr class="column-selector-parent">
                <?php foreach($dataProvider[1] as $key => $value) { ?>
                    <td class="text-center">
                        <?= ColumnFilterWidget::widget([
                                'inputName' => $key,
                                'columnsList' => $columnsName,
                            ]
                        ) ?>
                    </td>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dataProvider as $row) { ?>
                <tr>
                    <?php foreach($row as $element) { ?>
                        <td>
                            <?= $element ?>
                        </td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div class="row well-lg">
    <button class="btn btn-success" id="begin-import" disabled>
        Импортировать записи
    </button>
</div>

<?php

    $this->registerJs('
        // Количество колонок для импорта.
        let paramsCount = ' . count($columnsName) . ';
        let fileName = "' . $fileName . '";
        let encoding = "' . $encoding . '";
        let separator = "' . $separator . '";
        $(".column-selector").on("change", function(e) {
            // Получаем все селекторы столбцов.
            let selectors = $(e.target).closest(".column-selector-parent").find(".column-selector");
            // Длина массива селекторов.
            let selLength = selectors.length;
            // Проверка на дубли
            // let hasDuplicate = false;
            for (i = 0; i < selLength; i++) {
                let val_i = $(selectors[i]).val()
                let hasDuplicate = false;
                for(j = i + 1; j < selLength; j++) {
                    let val_j = $(selectors[j]).val();
                    if (val_i === val_j && val_i !== "") {
                        $(selectors[j]).addClass("has-duplicate");
                        hasDuplicate = true;
                    }
                }
            }
            // проверка на отсуствие дублей.
            let totalHasDuplicates = false;
            let paramsArray = [];
            for (i = 0; i < selLength; i++) {
                let hasDuplicate = false;
                let val_i = $(selectors[i]).val();
                if (val_i === "" && $(selectors[i]).hasClass("has-duplicate")) {
                    $(selectors[i]).removeClass("has-duplicate");
                    continue;
                }
                for(j = 0; j < selLength; j++) {
                    let val_j = $(selectors[j]).val();
                    if (val_i === val_j && val_i !== "" && i !== j) {
                        hasDuplicate = true;
                        totalHasDuplicates = true;
                    }    
                }
                // Если по столбцу не было дубликатов и столбец непустой.
                if (!hasDuplicate) {
                    if ($(selectors[i]).hasClass("has-duplicate")) {
                        $(selectors[i]).removeClass("has-duplicate");
                    }
                    // Добавляем значение к параметрам импорта. 
                    if (val_i !== "") {
                        paramsArray.push({data: $(selectors[i]).attr("name"), value: val_i});
                    }
                } 
            }
            // Если есть дублирование, или не все позиции выбраны, импорт данных невозможен.
            if (totalHasDuplicates || paramsArray.length  < paramsCount) {
                $("#begin-import").attr("disabled", true);
                $("#begin-import").removeAttr("data");
            } else {
                // Все столбцы распределены, нет дублей. Можно начать импорт.
                $("#begin-import").attr("disabled", false);
                $("#begin-import").attr("data", JSON.stringify(paramsArray));
            }
        })
    ', View::POS_READY);

    $this->registerJs('
        $("#begin-import").on("click", function() {
            window.location.href = "/site/save-to-table?"
                + "fileName=" + fileName
                + "&params=" + $(this).attr("data").toString() 
                + "&encoding=" + encoding
                + "&separator" + separator
            ;
            
        })
    ', View::POS_READY);
