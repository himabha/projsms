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
<div class="fsusertb-index">


    <?php Pjax::begin(['id' => 'pjax_callsnow']); ?>
    <div class="pull-right">
        <?php $form = ActiveForm::begin([
            'id' => 'search-form',
            'method' => 'get',
            'action' => ['dashboard'],
        ]); ?>
        <?= Html::textInput('caller_id',$caller_id,['class' => 'filter-input','placeholder' => 'Caller Number']); ?>
        <?= Html::textInput('called_no',$called_no,['class' => 'filter-input','placeholder' => 'Dialed Number']); ?>
        <button type="submit" class="btn btn-success">Filter</button>
        <?php ActiveForm::end(); ?>
    </div>
    <h1><?= Html::encode($this->title.' - '.$count) ?></h1>
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => [
                'id'=>'list_cld_tbl',
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
                    'header' => '<a href="#">Agent </a>',
                    'value' => function ($model)
                    {
                        if($model->cld->agent_id){
                          return $model->cld->users->username;
                        }
                        return null;
                    }
                ],
            ],
        ]); ?>
    </div>
    <?php Pjax::end(); ?>
</div>

<script type="text/javascript">
    setInterval(function(){
        $.pjax.reload({container: "#pjax_callsnow"});
    }, 300000);
</script>
