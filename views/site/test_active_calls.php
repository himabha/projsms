<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
/* @var $searchModel app\models\FsusertbSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Test Active Calls';
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title "><?= Html::encode($this->title . ' - ' . $count) ?></h4>
                        <!-- <p class="card-category"> Here is a subtitle for this table</p> -->
                    </div>
                    <div class="card-body">
                        <?php Pjax::begin(['id' => 'pjax_callsnow']); ?>
                        <div class="pull-right">
                            <?php $form = ActiveForm::begin([
                                'id' => 'search-form',
                                'method' => 'get',
                                'action' => ['index'],
                            ]); ?>
                            <?= Html::textInput('caller_id', $caller_id, ['class' => 'filter-input', 'placeholder' => 'Caller Id']); ?>
                            <?= Html::textInput('called_no', $called_no, ['class' => 'filter-input', 'placeholder' => 'Called Number']); ?>
                            <button type="submit" class="btn btn-success">Filter</button>
                            <?php ActiveForm::end(); ?>
                        </div>
                        <div class="table-responsive">
                            <?= GridView::widget([
                                'dataProvider' => $dataProvider,
                                'tableOptions' => [
                                    'id' => 'list_cld_tbl',
                                    'class' => 'table'
                                ],
                                'pager' => [
                                    'firstPageLabel' => 'First',
                                    'lastPageLabel' => 'Last',
                                    'maxButtonCount' => '2',
                                ],
                                'summary' => '',
                                'columns' => [
                                    'call_start',
                                    [
                                        'header' => '<a href="#">Caller Number</a>',
                                        'value' => function ($model) {
                                            return substr($model->ani, 0, 6) . 'XXXXX';
                                            //                		return $model->ani;
                                        }
                                    ],
                                    'dialed_number',
                                    'call_state',
                                    [
                                        'header' => '<a href="#">Call Duration</a>',
                                        'value' => 'callDuration',
                                    ],
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