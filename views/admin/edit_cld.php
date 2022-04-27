<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

?>
<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header card-header-primary">
						<h4 class="card-title ">Edit Cld</h4>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-sm-1">
							</div>
							<div class="col-sm-4 summary_box">
								<label>Available :</label>
								<label> <?= $summary['available'] ?></label>
							</div>
							<div class="col-sm-2">
							</div>
							<div class="col-sm-4 summary_box">
								<label>Assigned CIDs :</label>
								<label> <?= $summary['assigned'] ?></label>
							</div>
							<div class="col-sm-1">
							</div>
						</div>
						<?php $form = ActiveForm::begin(); ?>
						<?= $form->field($cld_Model, 'cld1')->widget(Select2::classname(), [
							'data' => $cld,
							//'value' => ['1'=>'919847512345'],
							'options' => ['placeholder' => '---Select---', 'multiple' => true, 'value' => $current_cld],
							'pluginOptions' => [
								'allowClear' => true
							],
						]); ?>
						<button class="btn btn-primary">Submit</button>
						<a href="<?= Url::toRoute(['admin/list-assign-cld']) ?>" class="btn btn-default pull-right">Back</a>
						<?php ActiveForm::end(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>