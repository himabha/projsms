<?php

use yii\helpers\Html;
use app\models\User;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
?>

<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header card-header-primary">
            <h4 class="card-title ">Test Numbers List</h4>
            <!-- <p class="card-category"> Here is a subtitle for this table</p> -->
          </div>
          <div class="card-body">
            <?php Pjax::begin(['id' => 'pjax_test_numbr']) ?>
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
                'summary' => '',
                'columns' => [
                  'Country',
                  'Test_Number',
                ],
              ]); ?>
            </div>
            <?php Pjax::end() ?>

            <?php
            $form = ActiveForm::begin(['id' => 'exportForm', 'method' => 'get', 'action' => ['export-test-number']]);
            ?>
            <?= Html::hiddenInput('Country', $Country, ['id' => 'export_Country']); ?>
            <?= Html::hiddenInput('Test_Number', $Test_Number, ['id' => 'export_Test_Number']); ?>
            <?= Html::submitButton('Export to Excel', ['class' => 'btn btn-success exprt_btn']) ?>
            <?php ActiveForm::end(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>