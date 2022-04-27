<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;


?>
<div class="row">
	<div class="col-sm-1">
	</div>
	<div class="col-sm-10">
		<h3>Change password</h3><br>

		<?php $form = ActiveForm::begin([
			'id' => 'changpass-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-6\">{input}{error}</div>\n",
            'labelOptions' => ['class' => 'col-lg-2 '],
        ],
		]); ?>

		<?= $form->field($model, 'new_pass')->passwordInput() ?>

		<?= $form->field($model, 'confirm_pas')->passwordInput() ?>

		<?= Html::submitButton('Update', ['class' => 'btn btn-primary']) ?>

		<?php ActiveForm::end(); ?>
		
	</div>
	<div class="col-sm-1">
	</div>
</div>


