<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\Fscallreport;


use kartik\daterange\DateRangePicker;
?>
<div class="row">
	<div class="col-sm-1">
	</div>
	<div class="col-sm-10">
		<h3>Resellerwise Summary</h3><br>
		<?php Pjax::begin(['id' => 'pjax_reseller_wise_report']) ?>

		<div class="summary_holder">
			<div class="row">
				<div class="col-sm-3 col-xs-12">
					<div class="summary_sm-box">
						<strong>Total Calls</strong><br>
						<?= !empty($totalColls) ? $totalColls: 0; ?>
					</div>

				</div>

<!--				<div class="col-sm-3 col-xs-12">
					<div class="summary_sm-box">
						<strong>Charges</strong><br>
						<?= !empty($Charges) ? $Charges: 0; ?>
					</div>
				</div>
-->
				<div class="col-sm-3 col-xs-12">
					<div class="summary_sm-box">
						<strong>Totat Duration</strong><br>
						<?= !empty($Call_Duration) ? $Call_Duration: 0; ?>
					</div>

				</div>
				<div class="col-sm-3 col-xs-12">
					<div class="summary_sm-box">
						<strong>Total Revenue</strong><br>
						$ <?= !empty($Cost) ? $Cost : 0; ?>
					</div>

				</div>
			</div>
		</div>
		<div style="clear: both;"></div>

		<?= Html::beginForm(['reseller-admin/reseller-summary'],'get') ?>
		<div class="row margin_20">
			<div class="col-sm-4">
				<?php
				echo DateRangePicker::widget([
					'name'=>'date_range',
					'value'=> $date_range,
					'convertFormat'=>true,
					'presetDropdown'=>true,
					'pluginOptions'=>[
						'locale'=>[
							'format'=>'Y-m-d',
							'separator'=>' to '
						],
						'opens' => 'right',
					],
				]);
				?>
			</div>
			<div class="col-sm-3">
				<?= Select2::widget([
						'id' => 'reseller',
						'name' => 'reseller',
						'value' => $reseller_id,
						'data' => ArrayHelper::map($reseller,'reseller_id','reseller.username'),
						'options' => [
							'placeholder' => 'Reseller',
						],
					]); ?>
			</div>
			<div class="col-sm-3">
				<?= Select2::widget([
						'id' => 'country',
						'name' => 'country',
						'value' => $country_id,
						'data' => ArrayHelper::map($country,'Country','Country'),
						'options' => [
							'placeholder' => 'Country',
						],
					]); ?>
			</div>
			<div class="col-sm-2">
				<?= Html::submitButton('Filter', ['class' => 'btn btn-success']) ?>
				<?= Html::a('Reset',['agent-summary'],['class' => 'btn btn-primary']); ?>
			</div>
		</div>

		<?= Html::endForm() ?>

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
						'header' => '<a href="#">Reseller Name</a>',
						'value' => 'reseller.username',
					],

					[
						'header' => '<a href="#">Country</a>',
						'value' => 'Country',
					],
					[
						'header' => '<a href="#">Total Calls</a>',
						'value' => 'Total_Calls',
						'footer' => '<strong>Page Total : '.Fscallreport::getTotalCalls($dataProvider->models).'</strong>',
					],
					[
						'header' => '<a href="#">Total Minutes</a>',
						'value' => 'Call_Duration',
						'footer' => '<strong>Page Total : '.Fscallreport::getTotalMinutes($dataProvider->models).' Minutes</strong>',
					],
					/*[
						'header' => '<a href="#">Total Charges</a>',
						'value' => 'Charges',
						'footer' => '<strong>Total : &#162;'.Fscallreport::getTotalCharges($dataProvider->models).'</strong>',
					],*/
					[
						'header' => '<a href="#">Total Revenue</a>',
						'value' => 'cld1_cost',
						'footer' => '<strong>Page Total : '.Fscallreport::getTotalResellerCost($dataProvider->models).'</strong>',
					],
					/*[
						'header' => '<a href="#">Margin</a>',
						'value' => 'margin',
						'footer' => '<strong>Total : &#162;'.Fscallreport::getTotalMargin($dataProvider->models).'</strong>',
					],*/

					[
						'header' => '<a href="#">Total Sale</a>',
						'value' => 'cld2_cost',
						'footer' => '<strong>Page Total : '.Fscallreport::getTotalResellerSale($dataProvider->models).'</strong>',
					],
				],
			]); ?>
		</div>
		<?php Pjax::end() ?>

		<?php
			$form = ActiveForm::begin(['id' => 'exportForm','method'=>'get','action' => ['export-agent-summary']]);
			?>
			<?=  Html::hiddenInput('date_range', isset($_GET['date_range']) ? $_GET['date_range'] : '',['id' => 'export_date_range']); ?>
			<?=  Html::hiddenInput('reseller', isset($_GET['reseller']) ? $_GET['reseller'] : '',['id' => 'export_reseller']); ?>
			<?=  Html::hiddenInput('country', isset($_GET['country']) ? $_GET['country'] : '',['id' => 'export_country']); ?>

			<?= Html::submitButton('Export to Excel', ['class' => 'btn btn-success exprt_btn']) ?>
			<?php ActiveForm::end(); ?>
	</div>
	<div class="col-sm-1">
	</div>
</div>
<script type="text/javascript">
		if ($('body').find('.pagination').length == 0) {
			$('.exprt_btn').css('bottom','0px');
		}
	</script>
