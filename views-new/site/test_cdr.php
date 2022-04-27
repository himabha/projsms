<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\models\Fscdr;
use app\models\Fsmycdr;

use kartik\daterange\DateRangePicker;
?>
<div class="row">
	<div class="col-sm-1">
	</div>
	<div class="col-sm-10">
		<h3>Calls</h3><br>
		<?php Pjax::begin(['id' => 'pjax_cdr_list']) ?>


		<div class="margin_20">
			<?php
			$form = ActiveForm::begin(['id' => 'searchForm','method'=>'get','action' => ['test-cdr']]);
			?>
			<div class="date_from">
				<?php
				echo DateRangePicker::widget([
					'name'=>'date_range',
					'value' => $date,
					'presetDropdown'=>true,
					'convertFormat'=>true,
					'pluginOptions'=>[
						'locale'=>[
							'format'=>'Y-m-d'
						],
						'opens' => 'right',
					],

				])
				?>
			</div>
			<?= Html::textInput('caller_id',$caller_id,['class' => 'filter-input','placeholder' => 'Caller Id']); ?>

			<?= Html::textInput('called_no',$called_no,['class' => 'filter-input','placeholder' => 'Called Number']); ?>
			<button type="submit" class="btn btn-success">Filter</button>
			
			<?php ActiveForm::end(); ?>
		</div>

		<div class="table-responsive">
			<?= GridView::widget([
				'dataProvider' => $dataProvider,
				'tableOptions' => [
					'id'=>'list_cld_tbl',
				],
				'showFooter' => true,
				'summary' => '',
				'pager' => [
					'firstPageLabel' => 'First',
					'lastPageLabel' => 'Last',
					'maxButtonCount' => '2',
				],
				'columns' => [
					[
						'attribute' => 'call_startdate',
						'value' => function($model) {
							return date('d-M-y H:i:s',strtotime($model->call_startdate));
						}
					],
					[
						'header' => '<a href="#">Caller_id</a>',
						'value' => function ($model)
						{
						//	return $model->ani;
                                                        return substr($model->ani,0,7).'XXXX';

						}
					],
					'called_number' , 
					'country', 
					[
						'attribute' => 'call_duration',
						'value' => function($model) {
							return round($model->call_duration/60,2).' Minutes';
						},
						'footer' => '<strong>Total : '.Fscdr::getTotalMin($dataProvider->models).' Minutes</strong>',
					],
				],
			]); ?>
		</div>
<!--
		<?php Pjax::end() ?>

		<?php
		$form = ActiveForm::begin(['id' => 'exportForm','method'=>'get','action' => ['export-data']]);
		?>
		<?=  Html::hiddenInput('export_date', $date,['id' => 'export_date']); ?>
		<?=  Html::hiddenInput('export_caller_id', $caller_id,['id' => 'export_caller_id']); ?>
		<?=  Html::hiddenInput('export_called_no', $called_no,['id' => 'export_called_no']); ?>

		<?= Html::submitButton('Export to Excel', ['class' => 'btn btn-success exprt_btn']) ?>
		<?php ActiveForm::end(); ?>
-->
		
	</div>
	<div class="col-sm-1">
	</div>
</div>

<!--
<script type="text/javascript">
	
	if ($('body').find('.pagination').length == 0) { 
		$('.exprt_btn').css('bottom','0px');
	}
</script>

-->
