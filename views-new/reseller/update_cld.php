<?php
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>
<div class="row">
	<div class="col-sm-12 col-md-10 col-lg-7">
		<div class="admin-add_user">
			<?php if (Yii::$app->session->hasFlash('user_add_failed')): ?>
				<div class="alert alert-danger alert-dismissable">
					<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
					<h4><i class="icon fa fa-check"></i> <?=Yii::$app->session->getFlash('user_add_failed')?></h4>
				</div>
			<?php endif;?>

			<?php
			$form = ActiveForm::begin(['id' => 'cldEditForm']);
			?>
			<div class="form-group">
				<label class="control-label">Assigned To</label>
				<input type="text" class="form-control" name="user" value="<?= !empty($model->cld) ? /*$model->cld->user->username */ $model->cld->username : ''; ?>" aria-invalid="false" readonly>
				<div class="help-block"></div>
			</div>

			<?= $form->field($model, 'inboundip')->hiddenInput()->label(false); ?>

			<?= $form->field($model, 'cld1')->textInput(['disabled'=> true])->label('Cld 1') ?>
			<?= $form->field($model, 'cld2')->hiddenInput()->label(false); ?>
			<?= $form->field($model, 'outboundip')->hiddenInput()->label(false); ?>
			<?= $form->field($model, 'cld1rate')->textInput(['disabled'=> true]); ?>
			<?= $form->field($model, 'cld2rate')->textInput(['disabled'=> true]); ?>
			<?= $form->field($model, 'cld3rate')->textInput() ?>

			<button class="btn btn-primary">Submit</button>
			<a href="<?= Url::toRoute(['reseller/add-cld']) ?>" class="btn btn-default pull-right">Close</a>

			<?php ActiveForm::end(); ?>

		</div>
	</div>
</div>
