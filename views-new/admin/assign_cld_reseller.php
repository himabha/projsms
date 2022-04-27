<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;

?>
<h3>Assign CLD to reseller</h3>
<?php if (Yii::$app->session->hasFlash('cld_added')): ?>
	<div class="alert alert-success alert-dismissable">
		<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
		<h4 class="flash-message"><i class="icon fa fa-check"></i> <?=Yii::$app->session->getFlash('cld_added')?></h4>
	</div>
<?php endif;?>
<div class="row">
	<div class="col-sm-12">

		<div class="box-outer">
			<div class="row">
				<div class="col-sm-1">
				</div>
				<div class="col-sm-4 summary_box">
					<label>Available :</label>
					<label> <?= $summary['available'] ?></label>
				</div>
				<div class="col-sm-2">
				</div>
				<div class="col-sm-4 summary_box">
					<label>Assigned CLDs :</label>
					<label> <?= $summary['assigned'] ?></label>
				</div>
				<div class="col-sm-1">
				</div>
			</div>

			<div id="user_nam_outr" class="form-group">
				<label class="control-label">Reseller</label>
				<?= Html::dropDownList('user_id','',$users,['id' => 'user_id','prompt' => '---select---' ,'class' => 'form-control']); ?>
				<div class="help-block user_hlpblk"></div>
			</div>

            <div class="help-block numbr-error_msg"></div>
            <button id="assign_nmbr" class="btn btn-primary">Submit</button>
            <a href="<?= Url::toRoute(['admin/add-cld-reseller']) ?>" class="btn btn-default pull-right">Back</a>
			<div class="grid_holdr">
				<?php $form = ActiveForm::begin(['method' => 'get','action' => ['assign-cld-reseller']]); ?>
				<div class="row">
					<div class="col-sm-5">
						<?= Html::textInput('search',$search,['class' => 'form-control','placeholder' => 'search']); ?>
					</div>
					<div class="col-sm-3">
						<?= Html::dropDownList('limit',$limit,[20 => 20,50=>50,100=>100],['id' => 'limit','prompt' => 'Limit' ,'class' => 'form-control']); ?>
					</div>
					<div class="col-sm-4">
						<button class="btn btn-primary">Search</button>
					</div>
				</div>
				<?php ActiveForm::end(); ?>

                <hr>

				<?= GridView::widget([
					'id' => 'asign_nmbr_grd',
					'dataProvider' => $dataProvider,
					//'summary' => '',
					'tableOptions' => [
						'class' => 'numbr_tbl',
					],
					'columns' => [
						[
							'class' => 'yii\grid\CheckboxColumn',
							'checkboxOptions' => function($model, $key, $index, $widget) {

								return ["value" => $model->cld1];

							}

						],
						[
							'label' => 'Number',
							'attribute' => 'cld1',
						],

					],
				]); ?>

			</div>


		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).on('click','#assign_nmbr',function () {
		var numbers = $('#asign_nmbr_grd').yiiGridView('getSelectedRows');
		var user = $('#user_id').val();
		var flag = true;
		if(numbers ==""){
			flag = false;
			$('.numbr-error_msg').text('Please select atleast one number from below list');
		} else {
			$('.numbr-error_msg').text('');
		}
		if(user ==""){
			flag = false;
			$('.user_hlpblk').text('User cannot be empty');
			$('#user_nam_outr').addClass('has-error');
		} else {
			$('.user_hlpblk').text('');
			$('#user_nam_outr').removeClass('has-error');
		}
		if (flag) {
			$(this).attr("disabled", true);
			var strvalue = "";
			$('input[name="selection[]"]:checked').each(function() {
				if(strvalue!="")
					strvalue = strvalue + ","+this.value;
				else
					strvalue = this.value;
			});
			$.ajax({
				url: baseurl + '?r=admin/assign-number-reseller',
				type: 'post',
				data: {user : user,numbers:strvalue},
				success: function (response) {
					location.reload();
				}
			});
		}
	});
</script>
