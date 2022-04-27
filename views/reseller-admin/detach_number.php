<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

?>
<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header card-header-primary">
						<h4 class="card-title ">Assigned clds to reseller</h4>
					</div>
					<div class="card-body">
						<?php Pjax::begin(['id' => 'pjax_cld_assigned']) ?>

						<?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['show-assigned']]); ?>

						<div class="row filter_outer">
							<div class="col-sm-3">
								<?= Html::dropDownList('reseller_id', $resellerId, $resellers, ['id' => 'reseller_id', 'prompt' => '---select---', 'class' => 'form-control']); ?>
							</div>
							<div class="col-sm-3">
								<?= Html::textInput('cld1', $cld1, ['class' => 'form-control', 'placeholder' => 'Cld1']); ?>
							</div>
							<div class="col-sm-3">
								<button type="submit" class="btn btn-success">Search</button>
							</div>
							<div class="col-sm-3">
								<button type="button" class="btn btn-danger" id="detach_selected_number" onclick="javascript:void(0);">Delete Selected Numbers</button>
							</div>
						</div>
						<?php ActiveForm::end(); ?>

						<div class="table-responsive">
							<?= GridView::widget([
								'id' => 'detach_num_grid',
								'dataProvider' => $dataProvider,
								'tableOptions' => [
									'id' => 'list_cld_tbl',
									'class' => 'table'
								],
								'showFooter' => true,
								'summary' => '',
								'pager' => [
									'firstPageLabel' => 'First',
									'lastPageLabel' => 'Last',
									'maxButtonCount' => '2',
								],
								'columns' => [
									[
										'class' => 'yii\grid\CheckboxColumn',
										'checkboxOptions' => function ($model, $key, $index, $column) {
											return ['value' => $model->cld1];
										}
									],
									'cld1',
									[
										'attribute' => 'reseller_id',
										'value' => function ($model) {
											return $model->reseller->username;
										}
									],
									'assigned_date',
									[
										'class' => 'yii\grid\ActionColumn',
										'template' => '{detach-cld}',
										'header' => 'Action',
										'buttons' => [
											'detach-cld' => function ($url, $model, $key) {
												return Html::button('<span class="glyphicon glyphicon-trash"></span>', ['class' => 'btn btn-danger btn-xs detach_btn', 'data-id' => $model->id, 'data-number' => $model->cld1, 'title' => 'Detach Number']);
											}
										],
									]
								],
							]); ?>
						</div>
						<?php Pjax::end() ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="detach_confirm" class="modal fade" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div id="detach_message"></div>
				<?php
				$form = ActiveForm::begin(['id' => 'detachForm', 'method' => 'post', 'action' => ['reseller-admin/detach-number']]);
				?>
				<!--<?= Html::hiddenInput('btn_id', '', ['id' => 'btn_id']); ?>-->
				<?= Html::hiddenInput('btn_number', '', ['id' => 'btn_number']); ?>

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
<script type="text/javascript">
	$(document).on('click', '.detach_btn', function() {
		var id = $(this).attr('data-id');
		var number = $(this).attr('data-number');
		$.ajax({
			url: baseurl + '?r=reseller-admin/check-callsend',
			method: 'POST',
			data: {
				id: id
			},
			success: function(response) {
				var response = JSON.parse(response);
				if (response.error) {
					alert(response.message);
				} else {
					//$('#btn_id').val(id);
					$('#btn_number').val(number);
					$('#detach_message').html(response.message);
					$('#detach_confirm').modal('show');
				}
			},
		});
	});
	$("#detach_selected_number").on("click", function() {
		var numbers = $('#detach_num_grid').yiiGridView('getSelectedRows');
		if (numbers.length > 0) {
			var strvalue = "";
			$('input[name="selection[]"]:checked').each(function() {
				if (strvalue != "")
					strvalue = strvalue + "," + this.value;
				else
					strvalue = this.value;
			});
			$('#btn_number').val(strvalue);
			$('#detach_message').html("Are you sure want to detach all selected numbers ?");
			$('#detach_confirm').modal('show');
		} else {
			alert("Please select atleast one number");
		}
	});
</script>