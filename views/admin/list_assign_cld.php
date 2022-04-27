<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'List Assigned CLDs';
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title "><?= Html::encode('List of Assigned CLDs') ?></h4>
                    </div>
                    <div class="card-body">
                        <?php Pjax::begin(); ?>
                        <div class="table-responsive">
                            <?= GridView::widget([
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'tableOptions' => [
                                    'id' => 'list_cld_tbl',
                                    'class' => 'table'
                                ],
                                'pager' => [
                                    'firstPageLabel' => 'First',
                                    'lastPageLabel' => 'Last',
                                    'maxButtonCount' => '2',
                                ],
                                'columns' => [
                                    'user_id',
                                    [
                                        'attribute' => 'user_name',
                                        'header' => '<a href="Javascript::void(0);">User Name</a>',
                                        'value' => 'user.username'
                                    ],
                                    'cld1',

                                    [
                                        'class' => 'yii\grid\ActionColumn',
                                        'template' => '{update-assigned-cld} {delete-assigned-cld}',
                                        'buttons' => [
                                            'update-assigned-cld' => function ($url, $model, $key) {
                                                return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
                                                    'class' => 'btn btn-success btn-xs',
                                                    'data-toggle' => 'tooltip',
                                                    'title' => 'Edit'
                                                ]);
                                            },
                                            'delete-assigned-cld' => function ($url, $model, $key) {
                                                return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                                                    'class' => 'btn btn-danger btn-xs',
                                                    'data-pjax' => "0",
                                                    'data-method' => 'post',
                                                    'data-confirm' => 'Are you sure you want to delete?',
                                                    'data-toggle' => 'tooltip',
                                                    'title' => 'Delete'
                                                ]);
                                            }
                                        ],
                                    ]
                                ],
                            ]); ?>
                        </div>
                        <?php Pjax::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>