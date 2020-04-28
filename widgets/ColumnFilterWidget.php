<?php

namespace app\widgets;

use yii\base\Widget;

class ColumnFilterWidget extends Widget
{
    public $columnsList;
    public $inputName;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        return $this->render('column-filter', [
            'inputName' => $this->inputName,
            'columnsList' => $this->columnsList,
        ]);
    }
}