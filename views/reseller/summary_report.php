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
$dr_start = '';
$dr_end = '';
if(!empty($_GET['TdrSearchSummary']['delivered_time']))
{
	$dr_arr = explode("to", $_GET['TdrSearchSummary']['delivered_time']);
	if(is_array($dr_arr) && count($dr_arr) > 0)
	{
		if(!empty(trim($dr_arr[0]))) $dr_start = trim($dr_arr[0]);
		if(!empty(trim($dr_arr[1]))) $dr_end = trim($dr_arr[1]);
	}
}
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
	var date_range = "";
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

		function doSearch() {
			const params = new URLSearchParams(location.search);
			params.append("TdrSearchSummary[billgroup_id]", $("#dd_billgroup_id").val());
			params.append("TdrSearchSummary[agent_id]", $("#dd_agent_id").val());
			//params.append("TdrSearchSummary[sender_id]", $("#dd_sender_id").val());
			params.append("TdrSearchSummary[delivered_time]", date_range);
			//window.history.replaceState({}, "", `${location.pathname}?${params.toString()}`);
			window.location.replace(`${location.pathname}?${params.toString()}`);
		}; 

		$("#dd_billgroup_id").change(function(){
			doSearch();
		});
		$("#dd_agent_id").change(function(){
			doSearch();
		});
		$("#btnClearRange").click(function(){
			$("#dr_from_date").val("");
			$("#dr_to_date").val("");
			date_range = "";
			doSearch();
		});
		$("#btnRefresh").click(function(){
			doSearch();
		});
		$("#dr_to_date").keyup(function() {
			$(this).trigger("change");
		});
		$("#dr_to_date").change(function(){
			let dr_from = $("#dr_from_date").val();
			let dr_to = $("#dr_to_date").val();
			if(dr_from != "" && dr_to != "")
			{
				if(moment(dr_to, "DD-MM-YYYY HH:mm") > moment(dr_from, "DD-MM-YYYY HH:mm"))
				{
					date_range = dr_from + " to " + dr_to;
					doSearch();
				} else {
					alert("From date must earlier than To date");
				}
			}
		});
		$("#btnToday").click(function(){
			let dt_from = moment().format("DD-MM-YYYY 00:00");
			let dt_to = moment().format("DD-MM-YYYY 23:59");
			$("#dr_from_date").val(dt_from);
			$("#dr_to_date").val(dt_to).trigger("change");
		});
		$("#btnYesterday").click(function(){
			let dt_from = moment().subtract(1, "day").format("DD-MM-YYYY 00:00");
			let dt_to = moment().subtract(1, "day").format("DD-MM-YYYY 23:59");
			$("#dr_from_date").val(dt_from);
			$("#dr_to_date").val(dt_to).trigger("change");
		});
		$("#btnLast7Days").click(function(){
			let dt_from = moment().subtract(7, "day").format("DD-MM-YYYY 00:00");
			let dt_to = moment().subtract(1, "day").format("DD-MM-YYYY 23:59");
			$("#dr_from_date").val(dt_from);
			$("#dr_to_date").val(dt_to).trigger("change");
		});
		$("#btnFullWeek").click(function(){
			let startLastWeek = moment().subtract(1, "week").startOf("isoWeek")
			let endLastWeek = moment().subtract(1, "week").endOf("isoWeek")

			let dt_from = startLastWeek.subtract(1, "day").format("DD-MM-YYYY 00:00");
			let dt_to = endLastWeek.subtract(1, "day").format("DD-MM-YYYY 23:59");
			$("#dr_from_date").val(dt_from);
			$("#dr_to_date").val(dt_to).trigger("change");
		});
		$("#btnThisMonth").click(function(){
			let dt_from = moment().startOf("month").format("DD-MM-YYYY 00:00");
			let dt_to = moment().endOf("month").format("DD-MM-YYYY 23:59");
			$("#dr_from_date").val(dt_from);
			$("#dr_to_date").val(dt_to).trigger("change");
		});
		$("#btnLastMonth").click(function(){
			let dt_from = moment().subtract(1, "month").startOf("month").format("DD-MM-YYYY 00:00");
			let dt_to = moment().subtract(1, "month").endOf("month").format("DD-MM-YYYY 23:59");
			$("#dr_from_date").val(dt_from);
			$("#dr_to_date").val(dt_to).trigger("change");
		});

	});
');
?>
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title ">Summary Report</h4>
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
                                <div class="col-md-2">
                                    <?php
									echo '<div class="input-group">';
									echo '<label>From';
									echo DateRangePicker::widget([
    									'id'=> 'dr_from_date',
    									'name'=> 'dr_from_date',
    									'value'=> $dr_start,
    									'useWithAddon'=>true,
    									'convertFormat'=>true,
    									'pluginOptions'=>[
        									'timePicker'=>true,
        									'timePickerIncrement'=>15,
        									'locale'=>['format' => 'd-m-Y h:m'],
        									'singleDatePicker'=>true,
        									'showDropdowns'=>true
    									]
									]);
									echo '<label>';
									echo '</div>';
									?>
                                </div>
                                <div class="col-md-2">
                                    <?php
									echo '<div class="input-group">';
									echo '<label>To';
									echo DateRangePicker::widget([
    									'id'=>'dr_to_date',
    									'name'=>'dr_to_date',
										'value'=> $dr_end,
    									'useWithAddon'=>true,
    									'convertFormat'=>true,
    									'pluginOptions'=>[
        									'timePicker'=>true,
        									'timePickerIncrement'=>15,
        									'locale'=>['format' => 'd-m-Y h:m'],
        									'singleDatePicker'=>true,
        									'showDropdowns'=>true
    									]
									]);
									echo '<label>';
									echo '</div>';
									?>
                                </div>
                                <div class="col-md-4">
                                    <?php
									echo Html::button('Clear Range', ['id' => 'btnClearRange', 'class' => 'btn btn-success']); 
									echo Html::button('Refresh', ['id' => 'btnRefresh', 'class' => 'btn btn-success']); 
									?>
                                </div>
                            </div>
                            <div style="margin-bottom:1em;">
                                <?= Html::button('TODAY', ['id' => 'btnToday', 'class' => 'btn btn-dark btn-sm']); ?>
                                <?= Html::button('YESTERDAY', ['id' => 'btnYesterday', 'class' => 'btn btn-dark btn-sm']); ?>
                                <?= Html::button('LAST 7 DAYS', ['id' => 'btnLast7Days', 'class' => 'btn btn-dark btn-sm']); ?>
                                <?= Html::button('LAST FULL WEEKS', ['id' => 'btnFullWeek', 'class' => 'btn btn-dark btn-sm']); ?>
                                <?= Html::button('THIS MONTH', ['id' => 'btnThisMonth', 'class' => 'btn btn-dark btn-sm']); ?>
                                <?= Html::button('LAST MONTH', ['id' => 'btnLastMonth', 'class' => 'btn btn-dark btn-sm']); ?>
                            </div>
                        </div>
                        <div id="dropdown_top">
                            <ul class="gv_top">
                                <li>
                                    <?= Html::dropdownlist('dd_billgroup_id',  isset($_GET['TdrSearchSummary']['billgroup_id']) ?  $_GET['TdrSearchSummary']['billgroup_id'] : ""  , $billgroups, ['id' => 'dd_billgroup_id', 'class' => 'btn-dark btn-sm', 'prompt' => 'Select Billgroup']); ?>
                                </li>
                                <li>
                                    <?= Html::dropdownlist('dd_agent_id',  isset($_GET['TdrSearchSummary']['agent_id']) ?  $_GET['TdrSearchSummary']['agent_id'] : ""  , $agents, ['id' => 'dd_agent_id', 'class' => 'btn-dark btn-sm', 'prompt' => 'Select Agent']); ?>
                                </li>
                                <li>
                                    <?php //= Html::dropdownlist('dd_sender_id',  isset($_GET['TdrSearchSummary']['sender_id']) ?  $_GET['TdrSearchSummary']['sender_id'] : ""  , $suppliers, ['id' => 'dd_sender_id', 'class' => 'btn-dark btn-sm', 'prompt' => 'Select Supplier']); ?>
                                </li>
                            </ul>
                        </div>
                        <div class="table-responsive">
                            <h4><b>Summary</b></h4>
                            <?= GridView::widget([
								'dataProvider' => $dataProvider,
								//'filterModel' => $searchModel,
								'tableOptions' => [
									'id' => 'list_cld_tbl',
									'class' => 'table'
								],
								'columns' => [
									[
										'attribute' => 'currency',
										'label' => 'Currency'
									],
									[
										'attribute' => 'msgs',
										'label' => 'Msgs',
									],
									[
										'attribute' => 'rev_in',
										'label' => 'Rev In'
									],
									[
										'attribute' => 'rev_out',
										'label' => 'Rev Out'
									],
									[
										'attribute' => 'profit',
										'label' => 'Profit'
									],
									[
										'attribute' => 'profit_percentage',
										'label' => '% Profit'
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