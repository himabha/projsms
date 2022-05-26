<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

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
		//$("#billgroup_id_search").attr("type", "hidden");
		$("#dd_billgroup_id").change(function(){
			$("#billgroup_id_search").val(jQuery(this).val()).trigger("change");
		});
		//$("#agent_id_search").attr("type", "hidden");
		$("#dd_agent_id").change(function(){
			$("#agent_id_search").val(jQuery(this).val()).trigger("change");
		});

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
	});
');
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title ">SMS Numbers</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div>
                                    <?php $form = ActiveForm::begin(['id' => 'searchForm', 'method' => 'get']); ?>
                                    <ul class="gv_top">
                                        <li>
                                            <?= Html::textInput('search', $search, ['id' => 'search_box', 'class' => 'search_box custom_search pull-left', 'placeholder' => 'Search....']); ?>
                                        </li>
                                        <li>
                                            <?= Html::dropdownlist('filter', $filter, ['10' => '10', '20' => '20', '50' => '50', '100' => '100', '1000' => '1000'], ['id' => 'filter_box', 'class' => 'filter_box custom_filter pull-left']); ?>
                                        </li>
                                        <!-- <li>
                                            <?php //= Html::a('Assign DDI to Agent', ['assign-cld'], ['class' => 'btn btn-success pull-left']) ?>
                                        </li> -->

                                        <li>
                                            <?= Html::a('Unallocate Agent', ['show-assigned'], ['class' => 'btn btn-success pull-left']) ?>
                                        </li>

                                        <!-- <li>
                                            <button type="button" class="btn btn-danger pull-left"
                                                id="edit_selected_number" onclick="javascript:void(0);">Edit Selected
                                                Numbers</button>
                                        </li> -->
                                    </ul>
                                    <?php ActiveForm::end(); ?>
                                </div>
                                <div id="dropdown_top">
                                    <ul class="gv_top">
                                        <li>
                                            <?= Html::dropdownList('dd_billgroup_id',  isset($_GET['FsmastertbSearch']['billgroup_id']) ?  $_GET['FsmastertbSearch']['billgroup_id'] : ""  , $billgroups, ['id' => 'dd_billgroup_id', 'class' => 'btn-dark btn-sm', 'prompt' => 'Select Bill Group', 'role' => 'button']); ?>
                                        </li>
                                        <li>
                                            <?= Html::dropdownlist('dd_agent_id',  isset($_GET['FsmastertbSearch']['agent_id']) ?  $_GET['FsmastertbSearch']['agent_id'] : ""  , $agents, ['id' => 'dd_agent_id', 'class' => 'btn-dark btn-sm', 'prompt' => 'Select Agent']); ?>
                                        </li>
                                    </ul>
                                </div>
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
												'class' => 'yii\grid\CheckboxColumn',
												'checkboxOptions' => function ($model, $key, $index, $column) {
													return ['value' => $model->fsmid];
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
												'label' => 'Agent Name',
												'filter' => $agents,
												'filterInputOptions' => [
													'id' => 'agent_id_search',
													'prompt' => 'Select Agent',
													'class' => 'custom_select'
												],												
												'attribute' => 'agent_id',
												'value' => function ($model) {
													return isset($model->users) ? $model->users->username : null;
												}
											],
											[
												'label' => 'Caller Number',
												'attribute' => 'cld1',
											],
											[
												'label' => 'Country',
												'attribute' => 'country_id',
												'filter' => $countries,
												'filterInputOptions' => [
													'prompt' => 'Select Country',
													'class' => 'custom_select'
												],
												'value' => function($model){
													return isset($model->country) ? $model->country->Country : null;
												}
											],
											[
												'label' => 'Cld1 Rate',
												'attribute' => 'cld1rate',
											],
											[
												'label' => 'Cld2 Rate',
												'attribute' => 'cld2rate',
											],
											[
												'label' => 'Cld3 Rate',
												'attribute' => 'cld3rate',
											],
												[
												'class' => 'yii\grid\ActionColumn',
												'header' => 'Action',
												'footer' => 'Total records: ' . $totalCount,
												'footerOptions' => ['style' => ['font-size' => 'larger', 'font-weight' => 'bold', 'min-width' => '10em']],
												'template' => ' {update-cld}', //{show-number-routes} ,  {delete-cld}
												'buttons' => [
													'show-number-routes' => function ($url, $model, $key) {
														return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, [
															'class' => 'btn btn-info btn-xs',
															'data-toggle' => 'tooltip',
															'title' => 'Show list of all resellers who hold this number',
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

										],
									]); ?>
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
                            <h4 class="float-right m-0">Update CLD3 Rate</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div id="detach_message"></div>
                            <?php
							$form = ActiveForm::begin(['id' => 'detachForm', 'method' => 'post', 'action' => ['reseller/edit-number']]);
							?>
                            <!-- <?= Html::hiddenInput('btn_id', '', ['id' => 'btn_id']); ?> -->
                            <?= Html::label('CLD3 Rate', 'cld3rate'); ?>
                            <?= Html::textInput('cld3rate', '', ['class' => "form-control"]); ?>
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