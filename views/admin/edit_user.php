<?php

use yii\widgets\ActiveForm;
?>

<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header card-header-primary">
						<h4 class="card-title ">Edit
							<?php if ($user->role === 2) {
								echo 'User';
							} else if ($user->role === 3) {
								echo 'Reseller';
							} else if ($user->role === 4) {
								echo 'Reseller Admin';
							} ?></h4>
					</div>
					<div class="card-body">
						<?php $form = ActiveForm::begin(['id' => 'userEditForm']); ?>
						<?= $form->field($user, 'username')->textInput() ?>
						<?= $form->field($user, 'email')->textInput() ?>
						<?= $form->field($user, 'account')->textInput() ?>
						<?= $form->field($user, 'edit_pas')->passwordInput() ?>
						<button class="btn btn-primary">Submit</button>
						<a href="<?= Yii::$app->request->referrer; ?>" class="btn btn-default pull-right">Close</a>
						<?php ActiveForm::end(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>