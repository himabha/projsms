<?php
use yii\widgets\ActiveForm;
?>
<div class="row">
	<div class="col-sm-12 col-md-10 col-lg-7">
		<div class="admin-add_user">
			<h3>Edit User</h3>
			<br>

		<?php
		$form = ActiveForm::begin(['id' => 'userEditForm']);
		?>

		<?= $form->field($user, 'username')->textInput() ?>
		<?= $form->field($user, 'email')->textInput() ?>
		<?= $form->field($user, 'account')->textInput() ?>
		<?= $form->field($user, 'edit_pas')->passwordInput() ?>
		
		<button class="btn btn-primary">Submit</button>
		<a href="<?= Yii::$app->request->referrer;?>" class="btn btn-default pull-right">Close</a>

		<?php ActiveForm::end(); ?>
	
</div>
	</div>
</div>

