<?php
use yii\helpers\Html;
use app\models\User;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
?>
<div class="row">
  <div class="col-sm-1">
  </div>
  <div class="col-sm-10">
    <h3>Test Numbers List</h3><br>
    <!-- <table align="center" class="testnmbr_tbl">
      <tr>
        <td colspan="3">
          To see <strong>Active Calls</strong> Please login as below
          <a href="http://portal.didgw.com/index.php?r=site%2Flogin">http://portal.didgw.com/index.php?r=site%2Flogin</a>
        </td>
      </tr>
      <tr>
        <th>Username</th>
        <td>:</td>
        <td>tsagnt</td>
      </tr>
      <tr>
        <th>Account</th>
        <td>:</td>
        <td>ddigwtst</td>
      </tr>
      <tr>
        <th>Password</th>
        <td>:</td>
        <td>testddi43$</td>
      </tr>
    </table> -->
    
    <?php Pjax::begin(['id' => 'pjax_test_numbr']) ?>
    <div class="table-responsive">
      <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => [
          'id'=>'list_cld_tbl',
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
    <br>
    <?php Pjax::end() ?>

    <?php
    $form = ActiveForm::begin(['id' => 'exportForm','method'=>'get','action' => ['export-test-number']]);
    ?>
    <?=  Html::hiddenInput('Country', $Country,['id' => 'export_Country']); ?>
    <?=  Html::hiddenInput('Test_Number', $Test_Number,['id' => 'export_Test_Number']); ?>
    <?= Html::submitButton('Export to Excel', ['class' => 'btn btn-success exprt_btn']) ?>
    <?php ActiveForm::end(); ?>
  </div>
  <div class="col-sm-1">
  </div>
</div>


