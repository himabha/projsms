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
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="material-datatables">
                            <?php Pjax::begin([
                                'enablePushState' => true,
                            ]); ?>
                            <?= GridView::widget([
                                'id' => 'users',
                                'filterPosition' => 'header',
                                'showFooter' => true,
                                'tableOptions' => [
                                    'class' => 'table table-striped table-no-bordered table-hover',
                                ],
                                'options'          => ['class' => 'table-responsive grid-view'],
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'columns' => [
                                    //'id',
                                    'name',
                                    [
                                        'attribute' => 'country_id',
                                        'filter' => $countries,
                                        'filterInputOptions' => ['prompt' => 'Select Country'],
                                        'value' => function($model){
                                            return $model->country->Country;
                                        }
                                    ],
                                    [
                                        'attribute' => 'countrynetwork_id',
                                        'filter' => $country_networks,
                                        'filterInputOptions' => ['prompt' => 'Select Country Network'],
                                        'value' => function($model){
                                            return $model->country->Country_Network;
                                        }
                                    ],
                                    [
                                        'attribute' => 'sender_id',
                                        'filter' => $suppliers,
                                        'filterInputOptions' => ['prompt' => 'Select Supplier'],
                                        'value' => function($model){
                                            return $model->supplier->name;
                                        }
                                    ],
                                    [
                                        'attribute' => 'currency_id',
                                        'filter' => $currencies,
                                        'filterInputOptions' => ['prompt' => 'Currency'],
                                        'value' => function($model){
                                            return $model->currency->currency;
                                        }
                                    ],
                                    [
                                        'attribute' => 'billcycle_id',
                                        'filter' => $billcycles,
                                        'filterInputOptions' => ['prompt' => 'Select Bill Cycle'],
                                        'value' => function($model){
                                            return $model->billcycle->billcycle;
                                        }
                                    ],
                                    [
                                        'attribute' => 'service',
                                        'filter' => $services,
                                        'filterInputOptions' => ['prompt' => 'Select Service'],
                                        'value' => function($model){
                                            return isset(\Yii::$app->params['services'][$model->service]) ? \Yii::$app->params['services'][$model->service] : '';
                                        }
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