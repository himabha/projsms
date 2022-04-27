<?php
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>
<div class="admin-add_cld">
	<div class="add_cls_summary">
		<div class="row">
			<div class="col-sm-4 col-xs-12">
				<div class="summary_sm-box">
					<strong>Stock</strong><br>
					<?= $summary['stock'] ?>
				</div>

			</div>
			<div class="col-sm-4 col-xs-12">
				<div class="summary_sm-box">
					<strong>Assigned CIDs</strong><br>
					<?= $summary['assigned'] ?>
				</div>

			</div>
			<div class="col-sm-4 col-xs-12">
				<div class="summary_sm-box">
					<strong>Available</strong><br>
					<?= $summary['available'] ?>
				</div>

			</div>
		</div>
	</div>
	<div style="clear: both;"></div>
	<div class="box-outer">
		<p>Please upload csv in the below format</p>
		
		<p class="hint">
			<a href="uploads/file/sample.csv" target="_blank">Sample.csv</a>
		</p>
		<?php
		$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);
		?>
		<?= $form->errorSummary($model); ?>
		<?= $form->field($model, 'file')->fileInput() ?>

		<button class="btn btn-primary">Submit</button>

		<?php ActiveForm::end(); ?>
		<?php if (Yii::$app->session->hasFlash('csv_failed')): ?>
			<div class="alert alert-danger alert-dismissable">
				<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
				<h4><i class="icon fa fa-check"></i> <?=Yii::$app->session->getFlash('csv_failed')?></h4>
			</div>
		<?php endif;?>
		<?php if (Yii::$app->session->hasFlash('csv_success')): ?>
			<div class="alert alert-success alert-dismissable">
				<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
				<h4><i class="icon fa fa-check"></i> <?=Yii::$app->session->getFlash('csv_success')?></h4>
			</div>
		<?php endif;?>
	</div>
	
</div>
