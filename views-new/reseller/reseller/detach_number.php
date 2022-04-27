<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

?>
<div class="row">
	<div class="col-sm-1">
	</div>
	<div class="col-sm-10">
		<h3>Assigned clds to user</h3><br>
		<?php Pjax::begin(['id' => 'pjax_cld_assigned']) ?>

		<?php $form = ActiveForm::begin(['method' => 'get','action' => ['show-assigned']]); ?>

		<div class="row filter_outer">
			<div class="col-sm-3">
				<?= Html::dropDownList('user_id',$userId,$users,['id' => 'user_id','prompt' => '---select---' ,'class' => 'form-control']); ?>
			</div>
			<div class="col-sm-3">
				<?= Html::textInput('cld1',$cld1,['class' => 'form-control','placeholder' => 'Cld1']); ?>
			</div>
			<div class="col-sm-3">
				<button type="submit" class="btn btn-success">Search</button>
			</div>
		</div>
		<?php ActiveForm::end(); ?>
		<hr>

		<div class="table-responsive">
			<?= GridView::widget([
				'dataProvider' => $dataProvider,
				'tableOptions' => [
					'id'=>'list_cld_tbl',
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
						'value' => function ($model)
						{
							return $model->user->username;
						}
					],
					'assigned_date',
					['class' => 'yii\grid\ActionColumn',
					'template' => '{detach-cld}',
					'buttons' => [
						'detach-cld' => function ($url, $model, $key) {
							// return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
							// 	'class' => 'btn btn-danger btn-xs',
							// 	'data-pjax' => "0",
							// 	'data-method' => 'post',
							// 	'data-confirm' => 'Are you sure you want to detach CLD1?',
							// 	'data-toggle' => 'tooltip',
							// 	'title' => 'Delete'
							// ]);

							return Html::button('<span class="glyphicon glyphicon-trash"></span>',['class' => 'btn btn-danger btn-xs detach_btn','data-id' => $model->id, 'data-number'=>$model->cld1, 'title' => 'Detach Number']);
						}
					],
				]
			],
		]); ?>
	</div>
	<?php Pjax::end() ?>
</div>
<div class="col-sm-1">
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
				$form = ActiveForm::begin(['id' => 'detachForm','method'=>'post','action' => ['reseller/detach-number']]);
				?>
				<?=  Html::hiddenInput('btn_id', '',['id' => 'btn_id']); ?>
                <?=  Html::hiddenInput('btn_number', '',['id' => 'btn_number']); ?>

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
	$(document).on('click','.detach_btn',function () {
		var id = $(this).attr('data-id');
        var number = $(this).attr('data-number');
		$.ajax({
			url: baseurl + '?r=reseller/check-callsend',
			method: 'POST',
			data: {id:id},
			success: function(response){
				if (response.error) {
					alert(response.message);
				} else {
					$('#btn_id').val(id);
                    $('#btn_number').val(number);
					$('#detach_message').html(response.message);
					$('#detach_confirm').modal('show');
				}
			},
		});
	})
</script>
