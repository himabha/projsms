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
                                    'name',
                                    'country_id',
                                    'countrynetwork_id',
                                    'sender_id',
                                    'currency_id',
                                    'billcycle_id',
                                    'service',
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