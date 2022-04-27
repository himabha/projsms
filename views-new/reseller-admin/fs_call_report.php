<?php
use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\daterange\DateRangePicker;
?>
<div class="row">

	<div class="col-sm-12">
		<h3>Reseller Detailed Report</h3>
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
						<strong>Total Charges</strong><br>
						<?= !empty($Charges) ? $Charges: 0; ?>
					</div>

				</div>
-->
				<div class="col-sm-3 col-xs-12">
					<div class="summary_sm-box">
						<strong>Total Duration</strong><br>
						<?= !empty($Call_Duration) ? $Call_Duration: 0; ?>
					</div>

				</div>
				<div class="col-sm-3 col-xs-12">
					<div class="summary_sm-box">
						<strong>Total Cost</strong><br>
						$ <?= !empty($Cost) ? $Cost : 0; ?>
					</div>

				</div>
				<div class="col-sm-3 col-xs-12">
					<div class="summary_sm-box">
						<strong>Total Sale</strong><br>
						$ <?= !empty($Sale) ? $Sale : 0; ?>
					</div>

				</div>
			</div>
		</div>
		<div style="clear: both;"></div>
		<?= Html::beginForm(['reseller-admin/fs-call-report'],'get') ?>
		<div class="row">
			<div class="col-sm-3">
				<?php
				echo DateRangePicker::widget([
					'name'=>'date_range',
					'presetDropdown'=>true,
					'value'=> $Datepickr,
					'pluginOptions'=>[
						'locale'=>[
							'format'=>'YYYY-MM-DD'
						],
						'opens' => 'right',
					],
					'pluginEvents' => [
						'apply.daterangepicker' => 'function(ev, picker) {
							var start = picker.startDate.format("YYYY-MM-DD");
							var end = picker.endDate.format("YYYY-MM-DD");
							$.ajax({
								url: baseurl + "?r=reseller-admin/load-search-fields",
								type: "post",
								data: {start :start,end:end},
								success: function (response) {
									$("#reseller").html(response.reseller_optn);
									$("#country").html(response.country_optn);
									$("#caller_id").html(response.callerId_optn);
									$("#cld1").html(response.cld1_optn);
								}
								});
							}',
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
						'value' => $country_name,
						'data' => ArrayHelper::map($country,'Country','Country'),
						'options' => [
							'placeholder' => 'Country',
						],
					]); ?>

				</div>
				<div class="col-sm-3">
					<?= Select2::widget([
						'id' => 'caller_id',
						'name' => 'caller_id',
						'value' => $callerId,
						'data' => ArrayHelper::map($caller_id,'Caller_ID','Caller_ID'),
						'options' => [
							'placeholder' => 'Caller Id',
						],
					]); ?>
				</div>

			</div>
			<br>
			<div class="row">
				<div class="col-sm-3">
					<?= Html::textinput('called_num',$called_num,['class' => 'form-control','placeholder' => 'Called Number']) ?>
				</div>

<!--				<div class="col-sm-3">
					<?= Select2::widget([
						'id' => 'cld1',
						'name' => 'cld1',
						'value' => $cld_1,
						'data' => ArrayHelper::map($cld1,'Cld1','Cld1'),
						'options' => [
							'placeholder' => 'Cld1',
						],
					]); ?>
				</div>
				<div class="col-sm-3">
					<?= Html::textinput('cld1_rate',$cld1_rate,['class' => 'form-control','placeholder' => 'Cld1 Rate']) ?>
				</div>
				<div class="col-sm-3">
					<?= Html::textinput('cld2_rate',$cld2_rate,['class' => 'form-control','placeholder' => 'Cld2 Rate']) ?>
				</div>
-->

			</div>
			<br>
			<div class="rw-btn-holder">
					<?= Html::submitButton('Filter', ['class' => 'btn btn-success']) ?>
					<?= Html::a('Reset',['fs-call-report'],['class' => 'btn btn-primary']); ?>
				</div>

			<?= Html::endForm() ?>
			<br>

			<div class="table-responsive">
				<?= GridView::widget([
					'dataProvider' => $dataProvider,
					'tableOptions' => [
						'id'=>'list_cld_tbl',
					],

					'pager' => [
						'firstPageLabel' => 'First',
						'lastPageLabel' => 'Last',
						'maxButtonCount' => '2',
					],
					'columns' => [
						'Date',
						[
							'attribute' => 'reseller_id',
							'value' => 'reseller.username',
						],
						'Country',
						'Caller_ID',
						'called_number',
			//			'Cld1',
			//			'Cld1_Rate',
						'Cld2_Rate',
						'Total_Calls',
						'Call_Duration',
						//'Charges',
						[
							'header' => '<a href="#">Cost</a>',
							'value' => 'cld1_cost',
						],
						[
							'header' => '<a href="#">Sale</a>',
							'value' => 'cld2_cost',
						]
					],
				]); ?>
			</div>

			<?php
			$form = ActiveForm::begin(['id' => 'exportForm','method'=>'get','action' => ['export-fscall']]);
			?>
			<?=  Html::hiddenInput('date_range', isset($_GET['date_range']) ? $_GET['date_range'] : '',['id' => 'export_date_range']); ?>
			<?=  Html::hiddenInput('reseller', isset($_GET['reseller']) ? $_GET['reseller'] : '',['id' => 'export_reseller']); ?>
			<?=  Html::hiddenInput('country', isset($_GET['country']) ? $_GET['country'] : '',['id' => 'export_country']); ?>
			<?=  Html::hiddenInput('called_num', isset($_GET['called_num']) ? $_GET['called_num'] : '',['id' => 'export_called_num']); ?>
			<?=  Html::hiddenInput('caller_id', isset($_GET['caller_id']) ? $_GET['caller_id'] : '',['id' => 'export_caller_id']); ?>
<!--			<?=  Html::hiddenInput('cld1', isset($_GET['cld1']) ? $_GET['cld1'] : '',['id' => 'export_cld1']); ?>
			<?=  Html::hiddenInput('cld1_rate', isset($_GET['cld1_rate']) ? $_GET['cld1_rate'] : '',['id' => 'export_cld1_rate']); ?>
			<?=  Html::hiddenInput('cld2_rate', isset($_GET['cld2_rate']) ? $_GET['cld2_rate'] : '',['id' => 'export_cld2_rate']); ?>

-->			<?= Html::submitButton('Export to Excel', ['class' => 'btn btn-success exprt_btn']) ?>
			<?php ActiveForm::end(); ?>
		</div>

	</div>
	<script type="text/javascript">
		if ($('body').find('.pagination').length == 0) {
			$('.exprt_btn').css('bottom','0px');
		}
	</script>
