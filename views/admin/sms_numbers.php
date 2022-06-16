<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use kartik\dialog\Dialog;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 20;
$totalCount = $dataProvider->getTotalCount();
echo Dialog::widget();
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
	.form_select{
		line-height:2em;
		padding-left: 0.5em;
		padding-right: 0.5em;
		border-color:#cccccc;
	}
	.label_select{
		color:black;
		font-weight:bold;
		font-size:larger;
	}
	.form_control{
		padding-left: 0.5em;
		padding-right: 0.5em;
	},
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
	document.getElementById("allocate_revOutRate").addEventListener("change", function (e) {
    	if(this.value < 0.01){
      		this.value = "";
    	} else {
      		this.value = Math.round(+this.value * 100)/100;
    	}
	});
	$(document).ready(function(){
		$("#btnAllocate").click(function(){
			$("#allocated_message").empty().hide();
			let numbers = $("#manage_num_grid").yiiGridView("getSelectedRows");
			if (numbers.length > 0) {
				let strvalue = "";
				let allocated = false;
				$("input[name=\'selection[]\']:checked").each(function() {
					if($(this).attr("data-cld1") != "")
					{
						if (strvalue != "") strvalue = strvalue + "," + $(this).attr("data-cld1");
						else strvalue = $(this).attr("data-cld1");
					}
					if($(this).attr("data-admin_id") != 0) allocated = true;
				});
				$("#hdnAllocateNumbers").val(strvalue);
				if(allocated) {
					$("#allocated_message").html("Some of these numbers are allocated already.<br/>Continuing with this allocation will move all the numbers to the selected client").show();
				}
				if(strvalue != "") $("#allocate_numbers").modal("show");
				else BootstrapDialog.show({title:"Allocate Numbers", message:"Please select at least one unallocated item!"});
			} else {
				BootstrapDialog.show({title:"Allocate Numbers", message:"Please select at least one unallocated item!"});
			}
		});
		$("#btnUnallocate").click(function(){
			let numbers = $("#manage_num_grid").yiiGridView("getSelectedRows");
			$("#unallocated_message").empty().hide();
			if (numbers.length > 0) {
				let strvalue = "";
				let unallocated = false;
				$("input[name=\'selection[]\']:checked").each(function() {
					if($(this).attr("data-cld1") != "" && $(this).attr("data-admin_id") != 0)
					{
						if (strvalue != "") strvalue = strvalue + "," + $(this).attr("data-cld1");
						else strvalue = $(this).attr("data-cld1");
					}
					if($(this).attr("data-admin_id") == 0) unallocated = true;
				});
				if(unallocated) {
					$("#unallocated_message").html("Some of these numbers are not allocated yet and will be ignored.").show();
				}
				$("#hdnUnallocateNumbers").val(strvalue);
				if(strvalue != "") $("#unallocate_numbers").modal("show");
				else BootstrapDialog.show({title:"Unallocate Numbers", message:"Please select at least one allocated item!"});
			} else {
				BootstrapDialog.show({title:"Unallocate Numbers", message:"Please select at least one alllocated item!"});
			}
		});
		$("#dd_billgroup_id").change(function(){
			$("#billgroup_id_search").val(jQuery(this).val()).trigger("change");
		});
		$("#dd_sender_id").change(function(){
			$("#sender_id_search").val(jQuery(this).val()).trigger("change");
		});
		$("#dd_admin_id").change(function(){
			$("#admin_id_search").val(jQuery(this).val()).trigger("change");
		});
		$("#dd_service_id").change(function(){
			$("#service_id_search").val(jQuery(this).val()).trigger("change");
		});
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
                            <i class="material-icons">library_books</i>
                        </div>
                        <h4 class="card-title ">SMS Numbers</h4>
                    </div>
                    <div class="card-body">
                        <div>
                            <ul class="gv_top">
                                <li>
                                    <?= Html::button('Allocate Numbers', ['id' => 'btnAllocate','class' => 'btn btn-success pull-left']) ?>
                                    <?= Html::button('Unallocate Numbers', ['id' => 'btnUnallocate','class' => 'btn btn-danger pull-left']) ?>
                                    <?php //= Html::a('Allocate Numbers', ['assign-cld-reseller-admin'], ['class' => 'btn btn-success pull-left']) ?>
                                </li>
                                <li>
                                    <button type="button" class="btn btn-danger pull-left" id="edit_selected_number"
                                        onclick="javascript:void(0);">Edit Selected Numbers</button>
                                </li>
                            </ul>
                        </div>
                        <div id="dropdown_top">
                            <ul class="gv_top">
                                <li>
                                    <?= Html::dropdownList('dd_billgroup_id',  isset($_GET['FsmastertbSearch']['billgroup_id']) ?  $_GET['FsmastertbSearch']['billgroup_id'] : (!empty($bg->id) ? $bg->id : ""), $billgroups, ['id' => 'dd_billgroup_id', 'class' => 'btn-dark btn-sm', 'prompt' => 'Select Bill Group', 'role' => 'button']); ?>
                                </li>
                                <li>
                                    <?= Html::dropdownlist('dd_sender_id',  isset($_GET['FsmastertbSearch']['sender_id']) ?  $_GET['FsmastertbSearch']['sender_id'] : "", $suppliers, ['id' => 'dd_sender_id', 'class' => 'btn-dark btn-sm', 'prompt' => 'Select Supplier']); ?>
                                </li>
                                <li>
                                    <?= Html::dropdownlist('dd_admin_id',  isset($_GET['FsmastertbSearch']['admin_id']) ?  $_GET['FsmastertbSearch']['admin_id'] : "", $clients, ['id' => 'dd_admin_id', 'class' => 'btn-dark btn-sm', 'prompt' => 'Select Client']); ?>
                                </li>
                                <li>
                                    <?= Html::dropdownlist('dd_service_id',  isset($_GET['FsmastertbSearch']['service_id']) ?  $_GET['FsmastertbSearch']['service_id'] : "", $services, ['id' => 'dd_service_id', 'class' => 'btn-dark btn-sm', 'prompt' => 'Select Service']); ?>
                                </li>
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
										[
											'class' => 'yii\grid\CheckboxColumn',
											'checkboxOptions' => function ($model, $key, $index, $column) {
												return ['value' => $model->fsmid, 'data-cld1' => $model->cld1, 'data-admin_id' => $model->admin_id];
											}
										],
										[
											'label' => 'Bill Group',
											'attribute' => 'billgroup_id',
											'filter' => $billgroups,
											'filterInputOptions' => [
												'id' => 'billgroup_id_search',
												'prompt' => 'Select Bill Group',
												'class' => 'custom_select'
											],
											'value' => function ($model) {
												return isset($model->billgroup) ? $model->billgroup->name : null;
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
											'label' => 'Clients',
											'filter' => $clients,
											'filterInputOptions' => [
												'id' => 'admin_id_search',
												'prompt' => 'Select Client',
												'class' => 'custom_select'
											],
											'attribute' => 'admin_id',
											'value' => function ($model) {
												return $model->resellerAdmin ? $model->resellerAdmin->username : null;
											}
										],
										[
											'label' => 'Services',
											'attribute' => 'service_id',
											'filter' => $services,
											'filterInputOptions' => [
												'id' => 'service_id_search',
												'prompt' => 'Select Service',
												'class' => 'custom_select'
											],
											'value' => function ($model) {
												return isset(\Yii::$app->params['services'][$model->service_id]) ? \Yii::$app->params['services'][$model->service_id] : null;
											}
										],
										[
											'label' => 'Caller Number',
											'attribute' => 'cld1',
											//'filterInputOptions' => [
											//	'placeholder' => 'Search Caller Number',
											//]
										],
										[
											'label' => 'Reseller Name',
											'attribute' => 'reseller_id',
											'value' => function ($model) {
												if ($model->reseller_id !== 0) {
													return $model->resellers->username;
												} else {
													return '';
												}
											}
										],
										[
											'label' => 'Agent Name',
											'attribute' => 'agent_id',
											'value' => function ($model) {
												if ($model->agent_id !== 0) {
													return $model->users->username;
												} else {
													return '';
												}
											}
										],
										[
											'label' => 'Country',
											'attribute' => 'country_id',
											'filter' => $countries,
											'filterInputOptions' => [
												'prompt' => 'Select Country',
												'class' => 'custom_select'
											],
											'value' => function ($model) {
												return isset($model->country) ? $model->country->Country : null;
											}
										],
										[
											'label' => 'Rev. In Rate',
											'attribute' => 'cld1rate',
										],
										[
											'label' => 'Rev. Out Rate',
											'attribute' => 'cld2rate',
										],
										[
											'label' => 'Allocated Date',
											'attribute' => 'allocated_date',
										],
										/* [
											'class' => 'yii\grid\ActionColumn',
											'header' => 'Action',
											'footer' => 'Total records: ' . $totalCount,
											'footerOptions' => ['style' => ['font-size' => 'larger', 'font-weight' => 'bold', 'min-width' => '10em']],
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
										] */

									],
								]); ?>
                            </div>
                            <?php $form = ActiveForm::begin(['id' => 'exportForm', 'method' => 'get', 'action' => ['export-ddi']]); ?>
                            <?= Html::hiddenInput('search', $search, ['id' => 'search']); ?>
                            <?= Html::hiddenInput('filter', $filter, ['id' => 'filter']); ?>
                            <?= Html::submitButton('Export to Excel', ['class' => 'btn btn-success exprt_btn']) ?>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="manage_confirm" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="float-right m-0">Update CLD2 Rate</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="detach_message"></div>
                <?php
				$form = ActiveForm::begin(['id' => 'detachForm', 'method' => 'post', 'action' => ['admin/edit-number']]);
				?>
                <!-- <?= Html::hiddenInput('btn_id', '', ['id' => 'btn_id']); ?> -->
                <?= Html::label('CLD1 Rate', 'cld1Rate'); ?>
                <?= Html::textInput('cld1Rate', '', ['class' => "form-control"]); ?>
                <br />
                <?= Html::label('CLD2 Rate', 'cld2Rate'); ?>
                <?= Html::textInput('cld2Rate', '', ['class' => "form-control"]); ?>
                <?= Html::hiddenInput('btn_number', '', ['id' => 'btn_number']); ?>
                <div class="media form-group">
                    <button type="submit" class="btn btn-primary">Yes</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">No</button>

                    <?php ActiveForm::end(); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
</div>
<div id="allocate_numbers" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="float-right m-0">Allocate Numbers</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="allocated_message"
                    style="font-size:smaller; color:red; border:1px solid #ccc; padding:0.5em; margin-bottom:3em;">
                </div>
                <?= Html::beginForm(['/admin/allocate-numbers'], 'post', ['class' => 'form']) ?>
                <input type="hidden" name="hdnAllocateNumbers" id="hdnAllocateNumbers">
                <div class="form-group">
                    <label for="cboClient" class="label_select col-md-4 text-right">Client:</label>
                    <?= Html::dropDownList('cboClient', '', $clients_only, ['prompt' => 'Select Client', 'class' => 'form-select form_select', 'required' => 'required']) ?>
                </div>
                <div class="form-group">
                    <label for="cboClient" class="label_select col-md-4 text-right">Service:</label>
                    <?= Html::dropDownList('cboService', '', $services, ['prompt' => 'Select Service', 'id' => 'allocate_service_id', 'class' => 'form-select form_select', 'required' => 'required']) ?>
                </div>
                <div class="form-group">
                    <label for="revOutRate" class="label_select col-md-4 text-right">Rev In Rate:</label>
                    <?= Html::textInput('revInRate', isset($bg->cost_rate) ? $bg->cost_rate : 0 , ['class' => 'form-control-inline form_control', 'id' => 'allocate_revOutRate', 'type' => 'number', 'required' => 'required', 'step' => '0.01', 'min' => '0.01', 'disabled' => 'disabled']) ?>
                </div>
                <div class="form-group">
                    <label for="revOutRate" class="label_select col-md-4 text-right">Rev Out Rate:</label>
                    <?= Html::textInput('revOutRate', isset($bg->cld1rate) ? $bg->cld1rate : 0 , ['class' => 'form-control-inline form_control', 'id' => 'allocate_revOutRate', 'type' => 'number', 'required' => 'required', 'step' => '0.01', 'min' => '0.01']) ?>
                </div>
                <div class="form-group" style="text-align:center;">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>
<div id="unallocate_numbers" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="float-right m-0">Unallocate Numbers</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="unallocated_message"
                    style="font-size:smaller; color:red; border:1px solid #ccc; padding:0.5em; margin-bottom:3em;">
                </div>
                <?= Html::beginForm(['/admin/unallocate-numbers'], 'post', []) ?>
                <input type="hidden" name="hdnUnallocateNumbers" id="hdnUnallocateNumbers">
                <div class="form-group" style="text-align:center;">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>