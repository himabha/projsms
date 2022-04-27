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
						<h4 class="card-title ">Number Assigned to</h4>
						<!-- <p class="card-category"> Here is a subtitle for this table</p> -->
					</div>
					<div class="card-body">
						<?php Pjax::begin(['id' => 'pjax_cld_assigned']) ?>

						<div class="table-responsive">
							<?= GridView::widget([
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
									'cld1',
									[
										'attribute' => 'user_id',
										'value' => function ($model) {
											return $model->user->username;
										}
									],
									'assigned_date',
									'closing_date',
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
				$form = ActiveForm::begin(['id' => 'detachForm', 'method' => 'post', 'action' => ['admin/detach-number']]);
				?>
				<?= Html::hiddenInput('btn_id', '', ['id' => 'btn_id']); ?>

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
		$.ajax({
			url: baseurl + '?r=admin/check-callsend',
			method: 'POST',
			data: {
				id: id
			},
			success: function(response) {
				if (response.error) {
					alert(response.message);
				} else {
					$('#btn_id').val(id);
					$('#detach_message').html(response.message);
					$('#detach_confirm').modal('show');
				}
			},
		});
	})
</script>