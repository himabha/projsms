<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
?>
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">account_box</i>
                        </div>
                        <h4 class="card-title">
                            Bill groups
                            <div class="pull-right">
                                <?= Html::a(Html::tag('b', 'add', ['class' => 'material-icons']), ['create-billgroup'], [
                                    'class' => 'btn btn-xs btn-primary btn-round btn-fab',
                                    'rel' => "tooltip",
                                    'data' => [
                                        'placement' => 'bottom',
                                        'original-title' => 'Create Bill Group'
                                    ],
                                ]) ?>
                                <?= Html::a(Html::tag('b', 'upload', ['class' => 'material-icons']), ['upload-numbers'], [
                                    'class' => 'btn btn-xs btn-primary btn-round btn-fab',
                                    'rel' => "tooltip",
                                    'data' => [
                                        'placement' => 'bottom',
                                        'original-title' => 'Upload Numbers'
                                    ],
                                ]) ?>
                            </div>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="material-datatables">
                            <?php Pjax::begin([
                                'enablePushState' => true,
                            ]); ?>
                            <?= GridView::widget([
                                'id' => 'users',
                                'tableOptions' => [
                                    'class' => 'table table-striped table-no-bordered table-hover',
                                ],
                                'pager' => [
                                    'firstPageLabel' => 'First',
                                    'lastPageLabel' => 'Last',
                                    'maxButtonCount' => '2',
                                ],
                                'options'          => ['class' => 'table-responsive grid-view'],
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'columns' => [
                                    'id',
                                    [
                                        'attribute' => 'country_id',
                                        'filter' => $countries,
                                        'value' => function($model){
                                            return $model->country->Country;
                                        }
                                    ],
                                    [
                                        'attribute' => 'countrynetwork_id',
                                        'filter' => $country_networks,
                                        'value' => function($model){
                                            return $model->country->Country_Network;
                                        }
                                    ],
                                    [
                                        'attribute' => 'currency_id',
                                        'filter' => $currencies,
                                        'value' => function($model){
                                            return $model->currency->currency;
                                        }
                                    ],
                                    [
                                        'attribute' => 'billcycle_id',
                                        'filter' => $billcycles,
                                        'value' => function($model){
                                            return $model->billcycle->billcycle;
                                        }
                                    ],
                                    [
                                        'attribute' => 'service',
                                        'filter' => $services,
                                        'value' => function($model){
                                            return isset(\Yii::$app->params['services'][$model->service]) ? \Yii::$app->params['services'][$model->service] : '';
                                        }
                                    ],
                                    /* 'cost_rate',
                                    'cld1rate',
                                    'cld2rate',
                                    'cld3rate',
                                    'selfallocation',
                                    'maxperday', */
                                    [
                                        'header' => \Yii::t('app', 'Actions'),
                                        'class' => '\yii\grid\ActionColumn',
                                        'contentOptions' => [
                                            'class' => 'table-actions'
                                        ],
                                        'template' => '{update} {delete}',
                                        'buttons'  => [
                                            'update' => function ($url, $model) {
                                                return Html::a(
                                                    '<i class="fa fa-edit"></i>',
                                                    \yii\helpers\Url::to(['/admin/update-billgroup', 'id' => $model->id]),
                                                    [
                                                        /* 'rel'                      => "tooltip", */
                                                        'data-original-title'      => 'Edit this billgroup',
                                                        'data-placement'           => 'top',
                                                        'style'                    => 'margin-right: 10px'
                                                    ]
                                                );
                                            },
                                            'delete' => function ($url, $model) {
                                                return Html::a(
                                                    '<i class="fa fa-trash-o"></i>',
                                                    \yii\helpers\Url::to(['/admin/delete-billgroup', 'id' => $model->id]),
                                                    [
                                                        /* 'rel'                      => "tooltip", */
                                                        'data-original-title'      => 'Delete this billgroup?',
                                                        'data-placement'           => 'top',
                                                        'data-pjax'                => '0',
                                                        'data-confirm'             => 'Are you sure you want to delete this item?',
                                                        'data-method'              => 'post',
                                                        'style'                    => 'margin-right: 10px'
                                                    ]
                                                );
                                            },
                                        ]
                                    ],
                                ],
                            ]); ?>
                            <?php Pjax::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>