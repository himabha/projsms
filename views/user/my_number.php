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
');
$this->registerJs('
	$(document).ready(function(){
		$("#search_box").keyup(function() {
			if ($(this).val().length > 3) {
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
					<div class="card-header card-header-primary">
						<h4 class="card-title ">Manage DDI</h4>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-sm-12">
								<div class="row">
									<div class="col-sm-3 col-xs-6">
										<?= Html::a('Assign DDI to Agent', ['assign-cld'], ['class' => 'btn btn-success pull-left']) ?>
									</div>

									<div class="col-sm-3 col-xs-6">
										<?= Html::a('Detach DDI to Agent', ['show-assigned'], ['class' => 'btn btn-success pull-left']) ?>
									</div>

									<div class="col-sm-3 col-xs-6">
										<button type="button" class="btn btn-danger" id="edit_selected_number" onclick="javascript:void(0);">Edit Selected Numbers</button>
									</div>

									<div class="col-sm-3 col-xs-12">
										<?php
										$form = ActiveForm::begin(['id' => 'searchForm', 'method' => 'get']);
										?>
										<div class="pull_right-medium">
											<?= Html::textInput('search', $search, ['id' => 'search_box', 'class' => 'search_box', 'placeholder' => 'Search....']); ?>
											<?= Html::dropdownlist('filter', $filter, ['10' => '10', '20' => '20', '50' => '50', '100' => '100', '1000' => '1000'], ['id' => 'filter_box', 'class' => 'filter_box']); ?>
										</div>

										<?php ActiveForm::end(); ?>
									</div>
								</div>
								<div class="table-responsive">
									<?= GridView::widget([
										'id' => 'manage_num_grid',
										'dataProvider' => $dataProvider,
										'filterModel' => $searchModel,
										'filterPosition' => 'header',
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
												'filterInputOptions' => ['prompt' => 'Select Bill Group'],
												'value' => function ($model) {
													if ($model->billgroup_id !== 0) {
														return $model->billgroup->name;
													} else {
														return '';
													}
												}
											],
											[
												'label' => 'Caller Number',
												'attribute' => 'cld1',
												'filterInputOptions' => [
													'placeholder' => 'Search Caller Number',
												]
											],
											[
												'label' => 'Country Name',
												'attribute' => 'cld2description',
												'filterInputOptions' => [
													'placeholder' => 'Search Country Name',
												]
											],
											[
												'label' => 'Cld1 Rate',
												'attribute' => 'cld1rate',
												'filterInputOptions' => [
													'placeholder' => 'Search Cld1 Rate',
												]
											],
											[
												'label' => 'Cld2 Rate',
												'attribute' => 'cld2rate',
												'filterInputOptions' => [
													'placeholder' => 'Search Cld2 Rate',
												]
											],
											[
												'label' => 'Cld3 Rate',
												'attribute' => 'cld3rate',
												'footer' => 'Total records: ' . $totalCount,
												'footerOptions' => ['style' => ['font-size' => 'larger', 'font-weight' => 'bold']],
												'filterInputOptions' => [
													'placeholder' => 'Search Cld2 Rate',
												]
											],
												[
												'class' => 'yii\grid\ActionColumn',
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
