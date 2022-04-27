<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
?>
<div class="row">
	<div class="col-sm-2">
	</div>
	<div class="col-sm-8">
		<h3><?php
			if(isset($isReSellerAdmin) && $isReSellerAdmin){
				echo 'Reseller Admin';
			}
			else if(isset($isReSeller) && $isReSeller){
					echo 'Reseller';
			}else{
				echo 'User';
			} ?> List</h3>
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
					'id',
					'username',
					'email',
                                        'lastlogin',
                                        'userip',

					['class' => 'yii\grid\ActionColumn',
					'template' => '{edit-user}', // {delete-user}',
					'buttons' => [
						'edit-user' => function ($url, $model, $key) {
							return Html::a('<span class="glyphicon glyphicon-pencil"></span>',$url, [
								'class' => 'btn btn-success btn-xs',
								'data-toggle' => 'tooltip',
								'title' => 'Edit'
							]);
						},
						'delete-user' => function ($url, $model, $key) {
							return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
								'class' => 'btn btn-danger btn-xs',
								'data-pjax' => "0",
								'data-method' => 'post',
								'data-confirm' => 'Are you sure you want to delete User?',
								'data-toggle' => 'tooltip',
								'title' => 'Delete'
							]);
						}
					],
				]

			],
		]); ?>
	</div>
</div>
<div class="col-sm-2">
</div>
</div>
