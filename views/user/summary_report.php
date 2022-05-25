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
                                <div class="col-md-3">
                                    <?php
									echo '<label>Select date</label>';
									echo '<div class="input-group">';
									echo DateRangePicker::widget([
    									'id'=> 'dr_from_to_date',
    									'name'=> 'dr_from_to_date',
    									'value'=> isset($_GET['TdrSearchSummary']['delivered_time']) ? $_GET['TdrSearchSummary']['delivered_time'] : '',
    									'useWithAddon'=>true,
    									'convertFormat'=>true,
										'initRangeExpr' => true,
										'startAttribute' => 'start_date',
										'endAttribute' => 'end_date',
    									'pluginOptions'=>[
        									'locale'=>['format' => 'd-m-Y', 'separator' => ' to '],
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
                            </ul>
                        </div>
                        <div class="table-responsive">
                            <?= GridView::widget([
								'dataProvider' => $dataProvider,
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