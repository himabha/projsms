<?php

use yii\widgets\ActiveForm;
?>
<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header card-header-primary">
						<h4 class="card-title ">Edit Reseller</h4>
					</div>
					<div class="card-body">
						<?php
						$form = ActiveForm::begin(['id' => 'userEditForm']);
						?>

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