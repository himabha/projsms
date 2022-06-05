<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\daterange\DateRangePicker;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 20;
$totalCount = $dataProvider->getTotalCount(); 
$today = !isset($_GET['TdrSearchSummary']) && !isset($_GET['TdrSearchDetailed']) && !isset($_GET['search']) ? date('d-m-Y') . ' 00:00 AM'  . ' to ' . date('d-m-Y')  . ' 12:59 PM': '';
$qstr = isset($_GET) ? http_build_query(\Yii::$app->request->queryParams) : '';
$csv_url = 'tdr-detailed-export/?mode=csv&' . $qstr;
$xls_url = 'tdr-detailed-export/?mode=xls&' . $qstr;
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

	function doSearch() {
		const params = new URLSearchParams(location.search);
		params.append("TdrSearchSummary[billgroup_id]", document.getElementById("dd_billgroup_id").value);
		params.append("TdrSearchSummary[delivered_time]", document.getElementById("dr_from_to_date").value);
		window.location.replace(`${location.pathname}?${params.toString()}`);
	}; 

	$(document).ready(function(){
		$("#search_box").keyup(function() {
			if ($(this).val().length > 3) {
				$("#searchForm").submit();
			}
		});
		$("#search_box").focusout(function() {
			if($(this).val() == "") $("#searchForm").submit();
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
		if(today != "") {
			$("#dr_from_to_date").val(today);
			doSearch();
		}

		$("#dd_billgroup_id").change(function(){
			doSearch();
		});
		$("#btnRefresh").click(function(){
			doSearch();
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
                            <i class="material-icons">account_box</i>
                        </div>
                        <h4 class="card-title ">Detailed Report</h4>
                    </div>
                    <div class="card-body">
                        <div>
                            <?php $form = ActiveForm::begin(['id' => 'searchForm', 'method' => 'get']); ?>
                            <ul class="gv_top">
                                <li>
                                    <?= Html::textInput('search', $search, ['id' => 'search_box', 'class' => 'search_box custom_search pull-left', 'placeholder' => 'Search....']); ?>
                                </li>
                                <li>
                                    <?= Html::dropdownlist('filter', $filter, ['10' => '10', '20' => '20', '50' => '50', '100' => '100', '1000' => '1000'], ['id' => 'filter_box', 'class' => 'filter_box custom_filter pull-left']); ?>
                                </li>
                            </ul>
                            <?php ActiveForm::end(); ?>
                        </div>
                        <div>
                            <div class="row">
                                <div class="col-md-4">
                                    <?php
									echo '<label>Select date</label>';
									echo '<div class="input-group">';
									echo DateRangePicker::widget([
    									'id'=> 'dr_from_to_date',
    									'name'=> 'dr_from_to_date',
    									'value'=> isset($_GET['TdrSearchSummary']['delivered_time']) ? $_GET['TdrSearchSummary']['delivered_time'] : $today,
    									'useWithAddon'=>true,
    									'convertFormat'=>true,
										'initRangeExpr' => true,
										'startAttribute' => 'start_date',
										'endAttribute' => 'end_date',
    									'pluginOptions'=>[
											'timePicker'=>true,
											//'timePickerIncrement' => 15,
											'locale'=>['format' => 'd-m-Y H:i A', 'separator' => ' to '],
        									'showDropdowns'=>true,
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
													doSearch();
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
                                    <?= Html::dropdownlist('dd_billgroup_id',  isset($_GET['TdrSearchSummary']['billgroup_id']) ?  $_GET['TdrSearchSummary']['billgroup_id'] : ""  , $billgroups, ['id' => 'dd_billgroup_id', 'class' => 'btn btn-dark btn-sm', 'prompt' => 'Select Billgroup']); ?>
                                </li>
                                <li>
                                    <?= Html::button('Refresh', ['id' => 'btnRefresh', 'class' => 'btn btn-success btn-sm']); ?>
                                </li>
                                <li>
                                    <div class="dropdown show">
                                        <a class="btn btn-info dropdown-toggle btn-sm" href="#" role="button"
                                            id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            Export
                                        </a>

                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                            <a class="dropdown-item" href="<?= $csv_url; ?>">CSV</a>
                                            <a class="dropdown-item" href="<?= $xls_url; ?>">XLS</a>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="table-responsive">
                            <h4><b>Summary</b></h4>
                            <?= GridView::widget([
								'dataProvider' => $dataProvider,
								'tableOptions' => [
									'id' => 'list_cld_tbl',
									'class' => 'table'
								],
								'columns' => [
									[
										'attribute' => 'billgroup_id',
										'label' => 'Billgroup',
										'filter' => $billgroups,
										'filterInputOptions' => [
											'id' => 'billgroup_id_search',
											'prompt' => 'Select Bill Group',
											'class' => 'custom_select'
										],
										'value' => function($model)
										{
											return isset($model->billgroup) ? $model->billgroup->name : null;
										}
									],
									[
										'attribute' => 'currency',
										'label' => 'Currency'
									],
									[
										'attribute' => 'msgs',
										'label' => 'Msgs',
										'headerOptions' => ['style' => ['text-align' => 'right']],
										'contentOptions' => ['style' => ['text-align' => 'right']],
										'value' => function($model)
										{
											return number_format($model->msgs, 0);
										}
									],
									[
										'attribute' => 'rev_in',
										'label' => 'Rev In',
										'headerOptions' => ['style' => ['text-align' => 'right']],
										'contentOptions' => ['style' => ['text-align' => 'right']],
										'value' => function($model)
										{
											return $model->currency . number_format($model->rev_in, 2);
										}
									],
									// [
									// 	'attribute' => 'rev_out',
									// 	'label' => 'Rev Out',
									// 	'headerOptions' => ['style' => ['text-align' => 'right']],
									// 	'contentOptions' => ['style' => ['text-align' => 'right']],
									// 	'value' => function($model)
									// 	{
									// 		return $model->currency . number_format($model->rev_out, 2);
									// 	}
									// ],
									[
										'attribute' => 'profit',
										'label' => 'Profit',
										'headerOptions' => ['style' => ['text-align' => 'right']],
										'contentOptions' => ['style' => ['text-align' => 'right']],
										'value' => function($model)
										{
											return $model->currency . number_format($model->profit, 2);
										}
									],
									// [
									// 	'attribute' => 'profit_percentage',
									// 	'label' => '% Profit',
									// 	'headerOptions' => ['style' => ['text-align' => 'right']],
									// 	'contentOptions' => ['style' => ['text-align' => 'right']],
									// 	'value' => function($model)
									// 	{
									// 		return number_format($model->profit_percentage, 2);
									// 	}
									// ],								
								],
							]); ?>
                        </div>
                        <div class="table-responsive">
                            <h4><b>Results</b></h4>
                            <?= GridView::widget([
								'dataProvider' => $dataProvider_1,
								'tableOptions' => [
									'id' => 'list_result_tbl',
									'class' => 'table'
								],
								'columns' => [
									[
										'attribute' => 'countrynetwork_id',
										'label' => 'Country Network',
                                        'value' => function($model){
                                            return isset($model->country) ? $model->country->Country_Network: null;
                                        }
									],
									[
										'attribute' => 'billgroup_id',
										'label' => 'Billgroup',
										'value' => function($model)
										{
											return isset($model->billgroup) ? $model->billgroup->name : null;
										}
									],
									[
										'attribute' => 'from_number',
										'label' => 'CLI'
									],
									[
										'attribute' => 'to_number',
										'label' => 'BNUM'
									],
									[
										'attribute' => 'msgs',
										'label' => 'Total SMS',
										'headerOptions' => ['style' => ['text-align' => 'right']],
										'contentOptions' => ['style' => ['text-align' => 'right']],
									],
								],
							]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>