<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\models\Fscdr;

use kartik\daterange\DateRangePicker;
?>

<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header card-header-primary">
						<h4 class="card-title ">Datewise Report</h4>
					</div>
					<div class="card-body">
						<?php Pjax::begin(['id' => 'pjax_date_wise_report']) ?>

						<div class="table-responsive">
							<?= GridView::widget([
								'dataProvider' => $dataProvider,
								'tableOptions' => [
									'id' => 'list_cld_tbl',
									'class' => 'table'
								],
								'showFooter' => true,
								'summary' => '',
								'pager' => [
									'firstPageLabel' => 'First',
									'lastPageLabel' => 'Last',
									'maxButtonCount' => '2',
								],
								'columns' => [
									[
										'header' => '<a href="#">Date</a>',
										'value' => 'date',
									],

									[
										'header' => '<a href="#">Connected Calls</a>',
										'value' => 'call_count',
										'footer' => '<strong>Total : ' . Fscdr::getTotalCalls($dataProvider->models) . '</strong>',
									],
									[
										'header' => '<a href="#">Minutes</a>',
										'value' => function ($model) {
											return round($model['minute'] / 60, 2);
										},
										'footer' => '<strong>Total : ' . Fscdr::getTotalCalledMin($dataProvider->models) . ' Minutes</strong>',
									],
									[
										'header' => '<a href="#">Total Cost</a>',
										'value' => function ($model) {
											return round($model['sum'], 4);
										},
										'footer' => '<strong>Total : &#162;' . Fscdr::getTotalCost($dataProvider->models) . '</strong>',
									],
								],
							]); ?>
						</div>
						<?php Pjax::end() ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>