<?php

use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>
<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header card-header-primary">
						<h4 class="card-title ">Update CLD</h4>
					</div>
					<div class="card-body">
						<?php if (Yii::$app->session->hasFlash('user_add_failed')) : ?>
							<div class="alert alert-danger alert-dismissable">
								<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
								<h4><i class="icon fa fa-check"></i> <?= Yii::$app->session->getFlash('user_add_failed') ?></h4>
							</div>
						<?php endif; ?>
						<?php $form = ActiveForm::begin(['id' => 'cldEditForm']); ?>
						<div class="form-group">
							<label class="control-label">Assigned To</label>
							<?php
							$username = '';
							if ($model->agent_id > 0 && !empty($model->cld)) {
								$username = $model->cld->username;
							} else if ($model->admin_id > 0 && !empty($model->resellerAdminCld)) {
								$username = $model->resellerAdminCld->username;
							} else if ($model->reseller_id > 0 && $model->agent_id == 0 && !empty($model->resellerCld)) {
								$username = $model->resellerCld->username;
							} ?>
							<input type="text" class="form-control" name="user" value="<?php echo $username; ?>" aria-invalid="false" readonly>
							<div class="help-block"></div>
						</div>
						<?= $form->field($model, 'inboundip')->textInput() ?>
						<?= $form->field($model, 'cld1')->textInput()->label('Cld 1') ?>
						<?= $form->field($model, 'cld2')->textInput() ?>
						<?= $form->field($model, 'outboundip')->textInput() ?>
						<?= $form->field($model, 'cld1rate')->textInput() ?>
						<?= $form->field($model, 'cld2rate')->textInput() ?>
						<button class="btn btn-primary">Submit</button>
						<a href="<?= Url::toRoute(['admin/add-cld']) ?>" class="btn btn-default pull-right">Close</a>
						<?php ActiveForm::end(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>