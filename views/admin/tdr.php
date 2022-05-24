<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 20;
$totalCount = $dataProvider->getTotalCount(); 
$dr_start = '';
$dr_end = '';
if(!empty($_GET['TdrSearch']['delivered_time']))
{
	$dr_arr = explode("to", $_GET['TdrSearch']['delivered_time']);
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

		$("#dd_admin_id").change(function(){
			$("#admin_id_search").val(jQuery(this).val()).trigger("change");
		});
		$("#dd_sender_id").change(function(){
			$("#sender_id_search").val(jQuery(this).val()).trigger("change");
		});

		$("#btnClearRange").click(function(){
			$("#dr_from_date").val("");
			$("#dr_to_date").val("");
			$("#delivered_time_search").val("").trigger("change");
		});
		$("#btnRefresh").click(function(){
			$("#delivered_time_search").trigger("change");
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
					$("#delivered_time_search").val(dr_from + " to " + dr_to).trigger("change");
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
                        <h4 class="card-title ">SMS TDR</h4>
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
                                    <?= Html::dropdownlist('dd_admin_id',  isset($_GET['TdrSearch']['admin_id']) ?  $_GET['TdrSearch']['admin_id'] : ""  , $clients, ['id' => 'dd_admin_id', 'class' => 'btn-dark btn-sm', 'prompt' => 'Select Client']); ?>
                                </li>
                                <li>
                                    <?= Html::dropdownlist('dd_sender_id',  isset($_GET['TdrSearch']['sender_id']) ?  $_GET['TdrSearch']['sender_id'] : ""  , $suppliers, ['id' => 'dd_sender_id', 'class' => 'btn-dark btn-sm', 'prompt' => 'Select Supplier']); ?>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <div class="table-responsive">
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
                                        [
                                            'attribute' => 'id'
										],
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
											'label' => 'Clients',
											'attribute' => 'admin_id',
											'filter' => $clients,
											'filterInputOptions' => [
												'id' => 'admin_id_search',
												'prompt' => 'Select Client',
												'class' => 'custom_select'
											],
											'value' => function ($model) {
												return $model->resellerAdmin ? $model->resellerAdmin->username : null;
											}
										],
										[
											'label' => 'Suppliers',
											'attribute' => 'sender_id',
											'filter' => $suppliers,
											'filterInputOptions' => [
												'id' => 'sender_id_search',
												'prompt' => 'Select Supplier',
												'class' => 'custom_select'
											],
											'value' => function ($model) {
												return isset($model->supplier) ? $model->supplier->name : null;	
											}
										],
                                        [
                                            'attribute' => 'delivered_time',
											'value' => function($model)
											{
												if(isset($model->delivered_time)) 
												{
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