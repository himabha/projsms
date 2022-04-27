<?php
use yii\widgets\ActiveForm;
?>
<div class="row">
	<div class="col-sm-12 col-md-10 col-lg-7">
		<div class="admin-add_reseller">
			<h3>Add Reseller</h3>
			<br>
			<?php if (Yii::$app->session->hasFlash('reseller_add_success')): ?>
          <div class="alert alert-success alert-dismissable">
              <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
              <h4><i class="icon fa fa-check"></i> <?=Yii::$app->session->getFlash('reseller_add_success')?></h4>
          </div>
      <?php endif;?>
      <?php if (Yii::$app->session->hasFlash('reseller_add_failed')): ?>
          <div class="alert alert-danger alert-dismissable">
              <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
              <h4><i class="icon fa fa-check"></i> <?=Yii::$app->session->getFlash('reseller_add_failed')?></h4>
          </div>
      <?php endif;?>

		<?php
		$form = ActiveForm::begin(['id' => 'resellerAddForm']);
		?>

		<?= $form->field($reseller, 'username')->textInput() ?>
		<?= $form->field($reseller, 'email')->textInput() ?>
		<?= $form->field($reseller, 'account')->textInput() ?>
		<?= $form->field($reseller, 'password')->passwordInput() ?>

		<button class="btn btn-primary">Submit</button>
		<a href="<?= Yii::$app->request->referrer;?>" class="btn btn-default pull-right">Close</a>

		<?php ActiveForm::end(); ?>

</div>
	</div>
</div>
