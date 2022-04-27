<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

?>
<h3>Edit Cld</h3>
<div class="row">
	<div class="col-sm-12">
		
		<div class="box-outer">
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
			
			<?php
			$form = ActiveForm::begin();
			//   var_dump($current_cld); 
			// $aa[0] = '919847512345';
			// $aa[1] = '919847012345';
			//   //$cc = '919847512345,919847012345,';
			//  echo "<br>";
			//  var_dump($aa);
			//  echo "<br>";
			//  print_r($current_cld);
			//  echo "<br>";
			//  print_r($aa);

			//  $a=array_values($current_cld);
			//  print_r($current_cld);
			 //exit();
			  //$current_cld = '919847512345,919847012345,'
			 //$aa = $current_cld;
			?>
			
			<?= $form->field($cld_Model, 'cld1')->widget(Select2::classname(), [
				'data' =>$cld,
				//'value' => ['1'=>'919847512345'],
				'options' => ['placeholder' => '---Select---','multiple' => true,'value' => $current_cld],
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

