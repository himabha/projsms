<?= GridView::widget([
    'id' => 'asign_nmbr_grd',
    'dataProvider' => $dataProvider,
    //'summary' => '',
    'tableOptions' => [
        'class' => 'numbr_tbl',
    ],
    'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn',
            'checkboxOptions' => function($model, $key, $index, $widget) {

                return ["value" => $model->cld1];

            }

        ],
        [
            'label' => 'Number',
            'attribute' => 'cld1',
        ],

    ],
]); ?>
