<?php
use yii\widgets\ActiveForm;
?>
<div class="row">
	<div class="col-sm-12 col-md-10 col-lg-7">
		<div class="admin-add_user">
			<h3>Add <?php
				if(isset($isReSellerAdmin) && $isReSellerAdmin){
					echo 'Reseller Admin';
				}
				else if(isset($isReSeller) && $isReSeller){
						echo 'Reseller';
				}else{
					echo 'User';
				} ?></h3>
			<br>
			<?php if (Yii::$app->session->hasFlash('user_add_success')): ?>
                    <div class="alert alert-success alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <h4><i class="icon fa fa-check"></i> <?=Yii::$app->session->getFlash('user_add_success')?></h4>
                    </div>
                <?php endif;?>
                <?php if (Yii::$app->session->hasFlash('user_add_failed')): ?>
                    <div class="alert alert-danger alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <h4><i class="icon fa fa-check"></i> <?=Yii::$app->session->getFlash('user_add_failed')?></h4>
                    </div>
                <?php endif;?>

		<?php
		$form = ActiveForm::begin(['id' => 'userAddForm']);
		?>

		<?= $form->field($user, 'username')->textInput() ?>
		<?= $form->field($user, 'email')->textInput() ?>
		<?= $form->field($user, 'account')->textInput() ?>
		<?= $form->field($user, 'password')->passwordInput() ?>

		<button class="btn btn-primary">Submit</button>
		<a href="<?= Yii::$app->request->referrer;?>" class="btn btn-default pull-right">Close</a>

		<?php ActiveForm::end(); ?>

</div>
	</div>
</div>
