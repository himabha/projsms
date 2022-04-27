<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>
<div class="content">
	<div class="container-fluid">
		<div class="card ">
			<div class="card-header card-header-primary card-header-icon">
				<div class="card-icon">
					<i class="material-icons">account_box</i>
				</div>
				<h4 class="card-title">
					<span>Add Reseller</span>
					<div class="pull-right">
						<?= Html::a(Html::tag('b', 'keyboard_arrow_left', ['class' => 'material-icons']), ['reseller-admin/add-cld'], [
							'class' => 'btn btn-xs btn-success btn-round btn-fab',
							'rel' => "tooltip",
							'data' => [
								'placement' => 'bottom',
								'original-title' => 'Back'
							],
						]) ?>
					</div>
				</h4>
			</div>
			<div class="card-body">
				<?php
				$form = ActiveForm::begin(['id' => 'resellerAddForm']);
				?>
				<?= $form->field($reseller, 'username')->textInput() ?>
				<?= $form->field($reseller, 'email')->textInput() ?>
				<?= $form->field($reseller, 'account')->textInput() ?>
				<?= $form->field($reseller, 'password')->passwordInput() ?>
				<button class="btn btn-primary">Submit</button>
				<a href="<?= Yii::$app->request->referrer; ?>" class="btn btn-default pull-right">Close</a>
				<?php ActiveForm::end(); ?>
			</div>
		</div>
	</div>
</div>
</div>