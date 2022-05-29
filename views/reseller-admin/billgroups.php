<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$totalCount = $dataProvider->getTotalCount();
$this->registerCss('
    .custom_select{
        border:none;
    }
');

?>
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">content_paste</i>
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
                                'showFooter' => true,
                                'tableOptions' => [
                                    'class' => 'table table-striped table-no-bordered table-hover',
                                ],
                                'options'          => ['class' => 'table-responsive grid-view'],
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'columns' => [
                                    [
                                        'attribute' => 'id',
                                        'label' => 'Name',
                                        'filter' => $billgroups,
                                        'filterInputOptions' => [
                                            'prompt' => 'Select Name',
                                            'class' => 'custom_select'
                                        ],
                                        'value' => function ($model) {
                                            return $model->name;
                                        }
                                    ],
                                    [
                                        'attribute' => 'country_id',
                                        'filter' => $countries,
                                        'filterInputOptions' => [
                                            'prompt' => 'Select Country',
                                            'class' => 'custom_select'
                                        ],
                                        'value' => function ($model) {
                                            return $model->country->Country;
                                        }
                                    ],
                                    [
                                        'attribute' => 'countrynetwork_id',
                                        'filter' => $country_networks,
                                        'filterInputOptions' => [
                                            'prompt' => 'Select Country Network',
                                            'class' => 'custom_select'
                                        ],
                                        'value' => function ($model) {
                                            return $model->country->Country_Network;
                                        }
                                    ],
                                    [
                                        'attribute' => 'currency_id',
                                        'filter' => $currencies,
                                        'filterInputOptions' => [
                                            'prompt' => 'Select Currency',
                                            'class' => 'custom_select'
                                        ],
                                        'value' => function ($model) {
                                            return $model->currency->currency;
                                        }
                                    ],
                                    [
                                        'attribute' => 'billcycle_id',
                                        'filter' => $billcycles,
                                        'filterInputOptions' => [
                                            'prompt' => 'Select Bill Cycle',
                                            'class' => 'custom_select'
                                        ],
                                        'footerOptions' => ['style' => ['font-size' => 'larger', 'font-weight' => 'bold']],
                                        'footer' => 'Total records: ' . $totalCount,
                                        'value' => function ($model) {
                                            return $model->billcycle->billcycle;
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