<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 20;
$totalCount = $dataProvider->getTotalCount();
$today = !isset($_GET['TdrSearch']) && !isset($_GET['search']) ? date('d-m-Y') . ' 00:00 AM'  . ' to ' . date('d-m-Y')  . ' 12:59 PM' : '';
$qstr = isset($_GET) ? http_build_query(\Yii::$app->request->queryParams) : '';
$csv_url = 'tdr-export/?mode=csv&' . $qstr;
$xls_url = 'tdr-export/?mode=xls&' . $qstr;
$this->registerCss('
	.pagination {
		margin-left: 1em;
	}
	.pagination li{
		margin-right:1em;
	}
	.has-error.help-block, .help-block-error{
		color:red!important;
	}
	.custom_select{
		border:none;
		margin-right:2em;
	}
	input.custom_search{
		margin-bottom:6px;
		line-height:2.5em;
		padding-left:0.5em;
		padding-right:0.5em;
	}
	select.custom_filter{
		margin-bottom:6px;
		line-height:2.8em;
		padding-left:0.5em;
		padding-right:0.5em;
	}
	ul.gv_top{
		list-style-type:none;
		padding-left:0;
	}
	ul.gv_top li{
		display:inline-block;
	}
	#dropdown_top ul.gv_top select{
		margin-bottom:0.5em;
	}
');
$this->registerJs('
	$(document).ready(function(){
		$("#search_box").keyup(function() {
			if ($(this).val().length > 2 || !$(this).val().length) {
				$("#searchForm").submit();
			}
		});
		$(document).on("change", "#filter_box", function() {
			$("#searchForm").submit();
			$("input[name=\'per-page\']").val($(this).val());
		});
		$("#edit_selected_number").on("click", function() {
			var numbers = $("#manage_num_grid").yiiGridView("getSelectedRows");
			if (numbers.length > 0) {
				var strvalue = "";
				$("input[name=\'selection[]\']:checked").each(function() {
					if (strvalue != "")
						strvalue = strvalue + "," + this.value;
					else
						strvalue = this.value;
				});
				$("#btn_number").val(strvalue);
				$("#manage_confirm").modal("show");
			} else {
				alert("Please select at least one number");
			}
		});

		let today = "' . $today . '";
		if(today != "") $("#delivered_time_search").val(today).trigger("change");

		$("#dd_billgroup_id").change(function(){
			$("#billgroup_id_search").val(jQuery(this).val()).trigger("change");
		});
		$("#dd_reseller_id").change(function(){
			$("#reseller_id_search").val(jQuery(this).val()).trigger("change");
		});
		$("#btnRefresh").click(function(){
			$("#delivered_time_search").trigger("change");
		});
	});
');
?>
<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header card-header-primary card-header-icon">
						<div class="card-icon">
							<i class="material-icons">report</i>
						</div>
						<h4 class="card-title ">Test TDR</h4>
					</div>
					<div class="card-body">
						<div>
							<div class="row">
								<div class="col-md-4">
									<?php
									echo '<label>Select date</label>';
									echo '<div class="input-group">';
									echo DateRangePicker::widget([
										'id' => 'dr_from_to_date',
										'name' => 'dr_from_to_date',
										'value' => isset($_GET['TdrSearch']['delivered_time']) ? $_GET['TdrSearch']['delivered_time'] : $today,
										'useWithAddon' => true,
										'convertFormat' => true,
										'initRangeExpr' => true,
										'startAttribute' => 'start_date',
										'endAttribute' => 'end_date',
										'pluginOptions' => [
											'timePicker' => true,
											//'timePickerIncrement' => 15,
											'locale' => ['format' => 'd-m-Y H:i A', 'separator' => ' to '],
											'showDropdowns' => true,
											'ranges' => [
												"Today" => [
													"moment().startOf('day')",
													"moment().endOf('day')"
												],
												"Yesterday" => [
													"moment().startOf('day').subtract(1,'days')",
													"moment().endOf('day').subtract(1,'days')"
												],
												"Last 7 Days" => [
													"moment().subtract(7, 'day')",
													"moment().subtract(1, 'day')"
												],
												"Last Full Week" => [
													"moment().subtract(1, 'week').startOf('isoWeek').subtract(1, 'day')",
													"moment().subtract(1, 'week').endOf('isoWeek').subtract(1, 'day')"
												],
												"This Month" => [
													"moment().startOf('month')",
													"moment().endOf('month')"
												],
												"Last Month" => [
													"moment().subtract(1, 'month').startOf('month')",
													"moment().subtract(1, 'month').endOf('month')"
												],
											]
										],
										'pluginEvents' => [
											"apply.daterangepicker" => "function() { 
													$('#delivered_time_search').val($('#dr_from_to_date').val()).trigger('change');
												}",
										]
									]);
									echo '</div>';
									?>
								</div>
							</div>
						</div>
						<div id="dropdown_top" style="margin-top:1em;">
                            <ul class="gv_top">
                                <li>
                                    <?= Html::dropdownlist('dd_billgroup_id',  isset($_GET['TdrSearch']['billgroup_id']) ?  $_GET['TdrSearch']['billgroup_id'] : ""  , $billgroups, ['id' => 'dd_billgroup_id', 'class' => 'btn btn-dark btn-sm', 'prompt' => 'Select Billgroup']); ?>
                                </li>
                                <!-- <li>
                                    <?php //= Html::dropdownlist('dd_reseller_id',  isset($_GET['TdrSearch']['reseller_id']) ?  $_GET['TdrSearch']['reseller_id'] : ""  , $resellers, ['id' => 'dd_reseller_id', 'class' => 'btn btn-dark btn-sm', 'prompt' => 'Select Client']); ?>
                                </li> -->
                                <!-- <li>
                                    <?php //= Html::button('Refresh', ['id' => 'btnRefresh', 'class' => 'btn btn-success btn-sm']); ?>
                                </li> -->
                                <!-- <li>
                                    <div class="dropdown show">
                                        <a class="btn btn-info dropdown-toggle btn-sm" href="#" role="button"
                                            id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            Export
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                            <a class="dropdown-item" href="<?php //= $csv_url; ?>">CSV</a>
                                            <a class="dropdown-item" href="<?php //= $xls_url; ?>">XLS</a>
                                        </div>
                                    </div>
                                </li> -->
                            </ul>
                        </div>
						<div>
							<div class="table-responsive">
								<div class="pull-right">
									<ul class="gv_top">
										<?php $form = ActiveForm::begin(['id' => 'searchForm', 'method' => 'get']); ?>
										<li>
											<?= Html::textInput('search', $search, ['id' => 'search_box', 'class' => 'search_box custom_search pull-left', 'placeholder' => 'Search....']); ?>
										</li>
										<li>
											<?= Html::dropdownlist('filter', $filter, ['10' => '10', '20' => '20', '50' => '50', '100' => '100', '1000' => '1000'], ['id' => 'filter_box', 'class' => 'filter_box custom_filter pull-left']); ?>
										</li>
										<?php ActiveForm::end(); ?>
									</ul>
								</div>
								<?= GridView::widget([
									'id' => 'manage_num_grid',
									'dataProvider' => $dataProvider,
									'filterModel' => $searchModel,
									'showFooter' => true,
									'tableOptions' => [
										'id' => 'list_cld_tbl',
										'class' => 'table'
									],
									'columns' => [
										/* [
											'attribute' => 'id'
										], */
										[
											'attribute' => 'from_number'
										],
										[
											'attribute' => 'to_number'
										],
										[
											'attribute' => 'sms_message'
										],
										[
											'label' => 'Billgroup',
											'attribute' => 'billgroup_id',
											'filter' => $billgroups,
											'filterInputOptions' => [
												'id' => 'billgroup_id_search',
												'prompt' => 'Select Billgroup',
												'class' => 'custom_select'
											],
											'value' => function ($model) {
												return isset($model->billgroup) ? $model->billgroup->name : null;
											}
										],
										// [
										// 	'label' => 'Client',
										// 	'attribute' => 'reseller_id',
										// 	'filter' => $resellers,
										// 	'filterInputOptions' => [
										// 		'id' => 'reseller_id_search',
										// 		'prompt' => 'Select Client',
										// 		'class' => 'custom_select'
										// 	],
										// 	'value' => function ($model) {
										// 		return isset($model->resellers) ? $model->resellers->username : null;
										// 	}
										// ],
										[
											'attribute' => 'delivered_time',
											'value' => function ($model) {
												if (isset($model->delivered_time)) {
													return date('d-m-Y H:i:s', strtotime($model->delivered_time));
												} else {
													return null;
												}
											},
											'filterInputOptions' => [
												'id' => 'delivered_time_search',
												'class' => 'custom_select'
											],
											'footer' => 'Total records: ' . $totalCount,
											'footerOptions' => ['style' => ['font-weight' => 'bold']]
										]
										/*
										[
											'class' => 'yii\grid\CheckboxColumn',
											'checkboxOptions' => function ($model, $key, $index, $column) {
												return ['value' => $model->fsmid];
											}
										],
										[
											'class' => 'yii\grid\ActionColumn',
											'header' => 'Action',
											'footer' => 'Total records: ' . $totalCount,
											'footerOptions' => ['style' => ['font-size' => 'larger', 'font-weight' => 'bold', 'min-width'=> '10em']],
											'template' => '{update-cld}', // {show-number-routes} {delete-cld}',
											'buttons' => [
												'show-number-routes' => function ($url, $model, $key) {
													return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, [
														'class' => 'btn btn-info btn-xs',
														'data-toggle' => 'tooltip',
														'title' => 'Show list of all users who hold this number',
													]);
												},
												'update-cld' => function ($url, $model, $key) {
													return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
														'class' => 'btn btn-success btn-xs',
														'data-toggle' => 'tooltip',
														'title' => 'Edit'
													]);
												},
												'delete-cld' => function ($url, $model, $key) {
													return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
														'class' => 'btn btn-danger btn-xs',
														'data-pjax' => "0",
														'data-method' => 'post',
														'data-confirm' => 'Are you sure you want to delete CLD1?',
														'data-toggle' => 'tooltip',
														'title' => 'Delete'
													]);
												}
											],
										]
                                        */
									],
								]); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>