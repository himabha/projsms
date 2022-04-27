<?php
use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 20;
?>
<div class="row summary_outer">
	<div class="col-sm-4">
		<label>Stock :</label>
		<label> <?= $summary['stock'] ?></label>
	</div>
	<div class="col-sm-4">
		<label>Assigned CIDs :</label>
		<label> <?= $summary['assigned'] ?></label>
	</div>
	<div class="col-sm-4">

	</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<div class="row">
			<div class="col-sm-2 col-xs-12">
				<?=Html::a('Asign DDI to user', ['assign-cld'], ['class' => 'btn btn-success pull-left'])?>
			</div>
			<div class="col-sm-10 col-xs-12">
				<?php
				$form = ActiveForm::begin(['id' => 'searchForm','method'=>'get']);
				?>
				<div class="pull_right-medium">
					<?=  Html::textInput('search', $search,['id' => 'search_box','class' => 'search_box','placeholder' => 'Search....']); ?>
					<?=  Html::dropdownlist('filter', $filter,['20' => '20','50' => '50','100' => '100','1000' => '1000'],['id' => 'filter_box','class' => 'filter_box']); ?>
				</div>

				<?php ActiveForm::end(); ?>
			</div>
		</div>


		<div class="table-responsive">
			<?= GridView::widget([
				'dataProvider' => $dataProvider,
				'tableOptions' => [
					'id'=>'list_cld_tbl',
				],
				'summary' => '',
				'pager' => [
					'firstPageLabel' => 'First',
					'lastPageLabel' => 'Last',
					'maxButtonCount' => '2',
				],
				'columns' => [

					//'inboundip',
					'cld1',
                    [
                        'label' => 'Agent Name',
						'attribute' => 'agent_id',
						'value' => function ($model)
						{
                            if($model->agent_id !== 0){
    							return $model->users->username;
                            }else{
    							return '';
                            }
						}
					],
					//'cld2',
					//'outboundip',
					//'cld1description',
                    	[
                        	'label' => 'Country Name',
                        	'attribute' => 'cld2description',
                   	],
				//	'cld1rate',
			[	'label' => 'Rate Per Min',
				'attribute' => 'cld2rate',
			],
					// [
					// 	'header' => '<a href="Javascript::void(0);">User</a>',
					// 	'value' => 'cld.user.username'
					// ],
					['class' => 'yii\grid\ActionColumn',
					'template' => '{show-number-routes} {update-cld} {delete-cld}',
					'buttons' => [
						'show-number-routes' => function ($url, $model, $key) {
							return Html::a('<span class="glyphicon glyphicon-eye-open"></span>',$url, [
								'class' => 'btn btn-info btn-xs',
								'data-toggle' => 'tooltip',
								'title' => 'Show list of all users who hold this number',
							]);
						},
					],
				]

			],
		]); ?>
	</div>
</div>
</div>
<script type="text/javascript">
	$("#search_box").keyup(function(){
		if ($(this).val().length >3) {
			$('#searchForm').submit();
		}
	});
	$(document).on('change','#filter_box',function () {
		$('#searchForm').submit();
	})
</script>
