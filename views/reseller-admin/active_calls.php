<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
/* @var $searchModel app\models\FsusertbSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Active Calls';
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
                                'action' => ['dashboard'],
                            ]); ?>
                            <?= Html::textInput('caller_id', $caller_id, ['class' => 'filter-input', 'placeholder' => 'Caller Number']); ?>
                            <?= Html::textInput('called_no', $called_no, ['class' => 'filter-input', 'placeholder' => 'Dialed Number']); ?>
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
                                'summary' => '',
                                'pager' => [
                                    'firstPageLabel' => 'First',
                                    'lastPageLabel' => 'Last',
                                    'maxButtonCount' => '2',
                                ],
                                'columns' => [
                                    'call_start',
                                    'ani',
                                    'dialed_number',
                                    //                'cld1',
                                    //                'cld1_ratepersec',
                                    //                'cld2',
                                    'cld2_ratepersec',
                                    'call_state',
                                    [
                                        'header' => '<a href="#">Call Duration</a>',
                                        'value' => 'callDuration',
                                    ],
                                    [
                                        'header' => '<a href="#">Reseller </a>',
                                        'value' => function ($model) {
                                            if ($model->cld->reseller_id) {
                                                return $model->cld->resellers->username;
                                            }
                                            return null;
                                        }
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

<script type="text/javascript">
    setInterval(function() {
        $.pjax.reload({
            container: "#pjax_callsnow"
        });
    }, 300000);
</script>