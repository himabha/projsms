<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use kartik\daterange\DateRangePicker;

?>

<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header card-header-primary">
						<h4 class="card-title ">Traffic Summary</h4>
					</div>
					<div class="card-body">
						<div class="summary_holder">
							<div class="row">
								<div class="col-sm-3 col-xs-12">
									<div class="summary_sm-box">
										<strong>Total Calls</strong><br>
										<?= !empty($totalColls) ? $totalColls : 0; ?>
									</div>

								</div>
								<div class="col-sm-3 col-xs-12">
									<div class="summary_sm-box">
										<strong>Duration</strong><br>
										<?= !empty($Call_Duration) ? $Call_Duration : 0; ?>
									</div>

								</div>
								<div class="col-sm-3 col-xs-12">
									<div class="summary_sm-box">
										<strong>Total Cost</strong><br>
										$ <?= !empty($Cost) ? $Cost : 0; ?>
									</div>

								</div>
							<!--<div class="col-sm-3 col-xs-12">
									<div class="summary_sm-box">
										<strong>Cost</strong><br>
										$ <?= !empty($Cost) ? $Cost : 0; ?>
									</div>
								</div>-->
							</div>
						</div>
						<?= Html::beginForm(['user/fs-call-report'], 'get') ?>
						<div class="row">
							<div class="col-sm-3">
								<?php
								echo DateRangePicker::widget([
									'name' => 'date_range',
									'presetDropdown' => true,
									'value' => $Datepickr,
									'pluginOptions' => [
										'opens' => 'right',
										'locale' => [
											'format' => 'YYYY-MM-DD'
										],
									],
									'pluginEvents' => [
										'apply.daterangepicker' => 'function(ev, picker) {
							var start = picker.startDate.format("YYYY-MM-DD");
							var end = picker.endDate.format("YYYY-MM-DD");
							$.ajax({
								url: baseurl + "?r=user/load-search-fields",
								type: "post",
								data: {start :start,end:end},
								success: function (response) {
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
							<div class="col-sm-2">
								<?= Select2::widget([
									'id' => 'country',
									'name' => 'country',
									'value' => $country_name,
									'data' => ArrayHelper::map($country, 'Country', 'Country'),
									'options' => [
										'placeholder' => 'Country',
									],
								]); ?>
							</div>
							<div class="col-sm-2">

								<?= Select2::widget([
									'id' => 'caller_id',
									'name' => 'caller_id',
									'value' => $callerId,
									'data' => ArrayHelper::map($caller_id, 'Caller_ID', 'Caller_ID'),
									'options' => [
										'placeholder' => 'Caller Id',
									],
								]); ?>
							</div>
							<!--<div class="col-sm-2">
								<?= Select2::widget([
									'id' => 'cld1',
									'name' => 'cld1',
									'value' => $cld_1,
									'data' => ArrayHelper::map($cld1, 'Cld1', 'Cld1'),
									'options' => [
										'placeholder' => 'Cld1',
									],
								]); ?>
							</div>-->
						</div>
						<div class="row">
							<div class="col-sm-3">
								<?= Html::textinput('called_num', $called_num, ['class' => 'form-control', 'placeholder' => 'Called Number']) ?>
							</div>
							<!-- <div class="col-sm-2">
								<?= Html::textinput('cld1_rate', $cld1_rate, ['class' => 'form-control', 'placeholder' => 'Cld1 Rate']) ?>
							</div>
							<div class="col-sm-2">
								<?= Html::textinput('cld2_rate', $cld2_rate, ['class' => 'form-control', 'placeholder' => 'Cld2 Rate']) ?>
							</div>
							-->
							<div class="col-sm-2">
								<?= Html::submitButton('Filter', ['class' => 'btn btn-success']) ?>
								<?= Html::a('Reset', ['fs-call-report'], ['class' => 'btn btn-primary']); ?>
							</div>

						</div>
						<?= Html::endForm() ?>
						<br>
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
									'Date',
									'Country',
									'Caller_ID',
									'called_number',
									//					'Cld1',
									//					'Cld2_Rate',
									'Total_Calls',
									'Call_Duration',
									[
										'header' => '<a href="#">Cost</a>',
										'value' => 'cld3_cost',
									]
								],
							]); ?>
						</div>
						<?php
						$form = ActiveForm::begin(['id' => 'exportForm', 'method' => 'get', 'action' => ['export-fscall']]);
						?>
						<?= Html::hiddenInput('date_range', isset($_GET['date_range']) ? $_GET['date_range'] : '', ['id' => 'export_date_range']); ?>

						<?= Html::hiddenInput('country', isset($_GET['country']) ? $_GET['country'] : '', ['id' => 'export_country']); ?>
						<?= Html::hiddenInput('caller_id', isset($_GET['caller_id']) ? $_GET['caller_id'] : '', ['id' => 'export_caller_id']); ?>
						<?= Html::hiddenInput('called_num', isset($_GET['called_num']) ? $_GET['called_num'] : '', ['id' => 'export_called_num']); ?>
						<?= Html::hiddenInput('cld1', isset($_GET['cld1']) ? $_GET['cld1'] : '', ['id' => 'export_cld1']); ?>
						<!--		
							<?= Html::hiddenInput('cld1_rate', isset($_GET['cld1_rate']) ? $_GET['cld1_rate'] : '', ['id' => 'export_cld1_rate']); ?>
							<?= Html::hiddenInput('cld2_rate', isset($_GET['cld2_rate']) ? $_GET['cld2_rate'] : '', ['id' => 'export_cld2_rate']); ?>
						-->
						<?= Html::submitButton('Export to Excel', ['class' => 'btn btn-success exprt_btn']) ?>
						<?php ActiveForm::end(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	if ($('body').find('.pagination').length == 0) {
		$('.exprt_btn').css('bottom', '0px');
	}
</script>